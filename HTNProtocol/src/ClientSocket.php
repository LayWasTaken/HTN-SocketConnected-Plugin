<?php
namespace HTNProtocol;

use HTNProtocol\Packets\API\SendableRequest;
use HTNProtocol\Packets\API\SendableResponse;
use HTNProtocol\Packets\SendablePacket;
use HTNProtocol\Streams\Exceptions\InvalidDataException;
use HTNProtocol\Streams\JsonStream;
use HTNProtocol\Streams\Receivable;
use HTNProtocol\Streams\ReceivedJsonStream;
use pocketmine\plugin\Plugin;
use Socket;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
class ClientSocket
{
    private Socket $sock;
    private ReceivableConfig $cfg;
    private bool $isOn = false;
    private $std;
    const PACKET_TEMPLATE = ["packet_type" => "", "packet"=>[]];
    public function __construct(private String $host, private Int $port, private String $password, private String $clientName, Plugin $plugin, &$sock) {
        $this->cfg = new ReceivableConfig($plugin);
        $this->std = AwaitStd::init($plugin);
        $this->createNewSocket();
        $this->handleConnection();
        $this->cfg->registerReceivableClass("\HTNProtocol\Streams\JsonStreamClasses\Requests\GetPlayerInfo", "DiscordBot");        
        $this->cfg->registerReceivableClass("\HTNProtocol\Streams\JsonStreamClasses\Requests\PlayerPunish", "DiscordBot");
        $sock = $this->sock;
    }
        
    private function handleConnection()
    {
        $this->createNewSocket();
        if(!@socket_connect($this->sock, $this->host, $this->port)) {$this->isOn = false;return;}
        $login = json_encode([
            "name" => $this->clientName,
            "password" => $this->password
        ]);
        if(!@socket_send($this->sock, $login, strlen($login), 0)) {$this->isOn = false;return;}
        socket_set_nonblock($this->sock);
        $this->isOn = true;
        $this->handleSentData();
    }

    private function reconnect()
    {
        Await::f2c(function(){
            while(!$this->isOn){
                $this->handleConnection();
                if(!$this->isOn){yield from $this->std->sleep(4);continue;}
                break;
            }
        });
    }

    private function handleSentData()
    {
        Await::f2c(function() {
            while (true) {
                if(!$this->isOn) {
                    $this->reconnect();
                    break;
                }
                try {
                    $data = socket_read($this->sock, 8192, 0);
                    if(!$data) {yield from $this->std->sleep(1);continue;}
                    $json = json_decode($data, true);
                    if(!($json["data"] && $json["data_type"] && $json["from"])) yield from $this->std->sleep(1);
                    $data = $this->checkSentData($json["from"], $json["data_type"], $json["data"]);
                    if($json['api']){
                        if(!($json["api"] === "request" || $json["api"] === "response")) yield from $this->std->sleep(1);
                        if(!$json["id"]) yield from $this->std->sleep(1);
                        if(is_bool($data)) {echo "B";yield from $this->std->sleep(1); continue;}
                        var_dump(new ReceivedJsonStream($data, $json["data_type"], $json["from"], $json["api"], $json["id"]));
                        yield from $this->std->sleep(1);
                        continue;
                    }
                    var_dump(new ReceivedJsonStream($data, $json["data_type"], $json["from"]));
                } catch (\Throwable $th) {
                    if($th->getLine() == 67) continue;
                    if(str_contains($th->getMessage(), "socket_read()")) {
                        $this->isOn = false;
                        $this->reconnect();
                        break;
                    }
                    echo "\nline:" .$th->getLine() . "\nmsg: " . $th->getMessage() . "\nfile:" . $th->getFile() . "\ntype: " . get_class($th);
                    yield $this->std->sleep(1);
                }
            }
        });
    }

    /**
     * @return object|false it will return false if it has invalid data, it will return a object if everything is valid
     */
    private function checkSentData(string $from, string $dataType, array $data):Receivable|false
    {
        if(!$cfgData = @$this->cfg->getAll()[$dataType]) return false;
        if(!class_implements($cfgData["namespace"])["HTNProtocol\Streams\Receivable"]) return false;
        if(is_array($cfgData["acceptable_clients"])){
            foreach ($cfgData["acceptable_clients"] as $key => $value) {
                if($value === $from) return $this->checkIfValidClassObject($cfgData["namespace"], $data);
            }
            return false;
        }
        if($cfgData["acceptable_clients"] === $from) return $this->checkIfValidClassObject($cfgData["namespace"], $data);
        return false;
    }

    private function checkIfValidClassObject(string $class, array $obj):false|object
    {
        if(str_contains($class, "PunishmentTime")) var_dump($obj);
        $object = new $class;
        $reflectionClass = new \ReflectionClass($class);
        $classProperties = $reflectionClass->getProperties();
        foreach ($classProperties as $propKey => $property) {
            if(!$property->isPublic()) continue;
            $propertyType = $property->getType();
            $propertyName = $property->getName();
            if(!$propertyType->allowsNull()){
                if(!@$obj[$propertyName]) return false;
                if($propertyType instanceof \ReflectionUnionType) {
                    foreach ($propertyType->getTypes() as $tKey => $type) {
                        if(class_exists($type->getName())) {
                            if(!is_array($obj[$propertyName])) return false;
                            $recurved = $this->checkIfValidClassObject($type->getName(), $obj);
                            if(!$recurved) return false;
                            $object->{$propertyName} = $recurved;
                            unset($obj[$propertyName]);
                            continue;
                        }
                        try {
                            $object->{$propertyName} = $obj[$propertyName];
                        } catch (\Throwable $th) {
                            return false;
                        }
                        unset($obj[$propertyName]);
                    }
                    continue;
                }
                if(class_exists($propertyType->getName())) {
                    if(!is_array($obj[$propertyName])) return false;
                    var_dump($obj);
                    var_dump($propertyName);
                    $recurved = $this->checkIfValidClassObject($propertyType->getName(), $obj[$propertyName]);
                    if(!$recurved) return false;
                    try {
                        $object->{$propertyName} = $recurved;
                    } catch (\Throwable $th) {
                        return false;
                    }
                    unset($obj[$propertyName]);
                    continue;
                }
                try {
                    $object->{$propertyName} = $obj[$propertyName];
                } catch (\Throwable $th) {
                    return false;
                }
                unset($obj[$propertyName]);
            }
        }
        foreach ($obj as $key => $value) {
            echo "FOOOpc";
            if(!property_exists($class, $key)) return false;
            echo "FOOOp";
            $property = $reflectionClass->getProperty($key);
            $propertyType = $property->getType();
            if(!@$key) return false;
            echo "FOOOpcl";
            if($propertyType instanceof \ReflectionUnionType) {
                foreach ($propertyType->getTypes() as $tKey => $type) {
                    if(class_exists($type->getName())) {
                        if(!is_array($value)) return false;
                        echo "FOOOpcd";
                        $recurved = $this->checkIfValidClassObject($type->getName(), $value);
                        if(!$recurved) return false;
                        echo "FOOOpcp";
                        $object->{$key} = $recurved;
                        continue;
                    }
                    try {
                        $object->{$key} = $value;
                    } catch (\Throwable $th) {
                        echo "FOOOpclq";
                       return false;
                    }
                }
                continue;
            }
            if(class_exists($propertyType->getName())) {
                if(!is_array($value)) return false;
                $recurved = $this->checkIfValidClassObject($propertyType->getName(), $value);
                var_dump($recurved);
                // if(is_bool($recurved)) return false;
                $object->{$key} = $recurved;
                continue;
            }
            try {
                $object->{$key} = $value;
            } catch (\Throwable $th) {
                echo "FOOOpclqpo";
               return false;
            }
        }
        return $object;
    }

    private function createNewSocket(){
        $this->sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        return true;
    }

    public function sendJsonStream(\HTNProtocol\Streams\SendableJsonStream $json):bool{
        if(!$this->isOn) return false;
        $encodedJson = [
            "data_type" => $json->getDataType(),
            "data" => $json->getData(),
            "to" => $json->to
        ];
        if($json->getApiType()) $encodedJson = array_merge($encodedJson, ["api" => $json->getApiType(), "id" => $json->getId()]);
        $encodedJson = json_encode($encodedJson);
        try {
            @socket_send($this->sock, $encodedJson, strlen($encodedJson), 0);
        } catch (\Throwable $th) {
            return false;
        }
    }

}