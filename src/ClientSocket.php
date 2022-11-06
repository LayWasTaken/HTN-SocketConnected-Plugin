<?php
namespace HTNProtocol;

use Exception;
use pocketmine\plugin\Plugin;
use Ramsey\Uuid\Uuid;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;

class ClientSocket {
    /**
     * @var \Closure[] $requests
     */
    private array $requests;
    /**
     * @var \Closure[] $receivables
     */
    private array $receivables;
    private array $receivables_class;

    private \Threaded $input;
    private \Threaded $output;
    private SocketThread $thread;
    private AwaitStd $std;

    public function __construct(Plugin $plugin)
    {
        $this->receivables = [];
        $this->receivables_class = [];
        $this->input = new \Threaded;
        $this->output = new \Threaded;
        $this->std = AwaitStd::init($plugin);
        Await::f2c(function(){
            while (true) {
                $count = $this->output->count();
                $snapshot = $this->output->chunk($count > 50 ? 50 : $count);
                foreach ($snapshot as $key => $value) {
                    [$api, $type] = str_split($key, 3);
                    [$id, $dt] = str_split($type, 32);
                    if($api === "RES"){
                        if(!array_key_exists($id, $this->requests)) continue;
                        $this->requests[$id]->__invoke($value);
                        unset($this->requests[$id]);
                        continue;
                    }
                    $this->receivables[$dt]->__invoke($value);
                }
                yield from $this->std->sleep(1);
            }
        });
    }

    /**
     * @param object $data any object
     * @param string $data_type don't add it if the data_type is the same as the $data class name 
     */
    public function sendData(object $data, string $data_type = null){
        $type = $data_type ?? (new \ReflectionClass($data))->getShortName();
        $this->input["SEN"+Uuid::uuid4()+$type] = $data;
    }

    /**
     * @param object $data any object
     * @param string $data_type don't add it if the data_type is the same as the $data class name 
     */
    public function sendRequest(object $data, string $data_type = null){
        $uuid = Uuid::uuid4();
        $type = $data_type ?? (new \ReflectionClass($data))->getShortName();
        $this->input["REQ"+$uuid+$type] = $data;
        Await::f2c(function() use ($uuid){
            yield from $this->std->sleep(20*10);
            try { unset($this->requests[$uuid]); } finally {}
        });
        return $uuid;
    }

    /**
     * @param object $data any object
     * @param string $data_type don't add it if the data_type is the same as the $data class name 
     * @param int $id it should be a 32 long uuid v4 string
     */
    public function sendResponse(object $data, string $id, string $data_type = null){
        $type = $data_type ?? (new \ReflectionClass($data))->getShortName();
        $this->input["RES"+$id+$type] = $data;
    }

    public function addReceivable(string $class, \Closure $onReceive){
        if(!class_exists($class)) throw new Exception("Class doesn't exists");
        $shortname = (new \ReflectionClass($class))->getShortName();
        $this->receivables[$shortname] = $onReceive;
        $this->receivables_class[$shortname] = $class;
    }

    public function start(string $ip, int $port, string $token){ 
        $this->thread = new SocketThread($this->input, $this->output, $ip, $port, $token, $this->receivables);
        $this->thread->start(); 
    }

}