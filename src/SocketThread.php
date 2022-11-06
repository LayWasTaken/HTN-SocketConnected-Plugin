<?php
namespace HTNProtocol;

use WebSocket\Client;

class SocketThread extends \Thread {
    private Client $sock;
    /**
     * @var string[] $requests
     */
    private array $requests;

    public function __construct(
        private \Threaded $main_thread_input, 
        private \Threaded $output, 
        private string $ip,
        private int $port,
        private string $token,
        private array $receivables
    ) {}

    private function createConnection(): bool {
        require_once __DIR__ . '/../vendor/autoload.php';
        try {
            $this->sock = new Client("ws://{$this->host}:{$this->port}/");
            $this->sock->text($this->token);
            $this->output[0] = true;
        } catch (\Throwable $th) { return false; }
        return true;
    }

    public function run() {
        while (true) {
            if (!$this->createConnection()) continue;
            $this->sock->setTimeout(0.02);
            while ($this->sock->isConnected()) {
                $this->doMainThreadInput();
                $this->handleReceivedData();
            }
        }
    }

    private function doMainThreadInput()
    {
        $amount = count($this->main_thread_input);
        $inputs = $this->main_thread_input->chunk($amount > 50 ? 50 : $amount);
        foreach ($inputs as $key => $data) {
            if($key === 0) continue;
            unset($this->main_thread_input[$key]);
            [$api, $type] = str_split($key, 0, 3);
            [$id, $dt] = $type;
            if($api === "RES"){
                $this->sendResponse($id, $data, $dt);
                continue;
            }
            if($api === "REQ"){
                $this->sendRequest($id, $data, $dt);
                continue;
            }
            $this->sendData($data, $key);
        }
    }

    // prettier-ignore
    private function handleReceivedData() {
        try { $data = $this->sock->receive(); } finally {}
        $json = json_decode($data, true);
        $data = $json['data'];
        $dt = array_key_exists($json['data_type'], $this->receivables)
            ? $this->receivables[$json['data_type']]
            : false;
        if (!$dt) return false;
        if (array_key_exists('api', $json)) {
            [$api, $id] = str_split($json['api'], 3);
            if(!array_key_exists($id, $this->requests)) return false;
            if(!$this->requests[$id] === $dt) return false;
            if ($api === 'RES') {
                $data = $this->checkIfValidReceivable($dt, $data);
                unset($this->requests[$id]);
                if (!$data) return false;
                return true;
            }
            if($api === 'REQ'){
                $data = $this->checkIfValidReceivable($dt, $data);
                if (!$data) return false;
                $this->output[$json['api']] = $data;
                return true;
            }
            return false;
        }
        $data = $this->checkIfValidReceivable($dt, $data);
        if (!$data) return false;
    }

    private function checkIfValidReceivable(string $class, array $obj) : object|bool {
        try { $ref = new \ReflectionClass($class); } catch (\ReflectionException $e) { return false; }
        $new = $ref->newInstanceWithoutConstructor();
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC|\ReflectionProperty::IS_PROTECTED|\ReflectionProperty::IS_PRIVATE) as $p) {
            if (!isset($obj[$p->getName()]) && !$p->hasDefaultValue() && $p->getType() !== null && !$p->getType()->allowsNull()) return false;
            try { (function() use ($obj, $p, $new) {
                $new->{$p->getName()} = $obj[$p->getName()] ?? ($p->hasDefaultValue() ? $p->getDefaultValue() : null);
            })->call($new); } catch (\TypeError $e) { return false; }
        }
        return $new;
    }

    private function sendRequest(int $id, $data, string $data_type){
        $json = json_encode([
            "data" => $data,
            "data_type" => $data_type,
            "api" => "REQ$id"
        ]);
        try { $this->sock->text($json); } catch (\Throwable $th) { return false; }
    }

    private function sendResponse(int $id, $data, string $data_type){
        $json = json_encode([
            "data" => $data,
            "data_type" => $data_type,
            "api" => "RES$id"
        ]);
        try { $this->sock->text($json); } catch (\Throwable $th) { return false; }
    }

    private function sendData($data, string $data_type){
        $json = json_encode([
            "data" => $data,
            "data_type" => $data_type
        ]);
        try { $this->sock->text($json); } catch (\Throwable $th) { return false; }
    }
}
