<?php

namespace HTNProtocol;

use HTNProtocol\Events\DataReceivedEvent;
use HTNProtocol\Events\RequestReceivedEvent;
use HTNProtocol\Receivable;
use pocketmine\plugin\Plugin;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use WebSocket\Client;
class ClientSocket
{
    private Client $sock;
    private $std;
    /**
     * @var Request[] $requests
     */
    private array $requests = [];
    /**
     * @var Receivable[] $receivables
     */
    private array $receivables = [];

    public function __construct(
        private string $host,
        private int $port,
        private string $password,
        private string $clientName,
        private Plugin $plugin
    ) {
        $this->std = AwaitStd::init($plugin);
        $this->createConnection() ? 0 : $this->reconnect();
    }

    private function createConnection(): bool
    {
        require_once __DIR__ . "/../vendor/autoload.php";
        try {
            echo "ws://{$this->host}:{$this->port}";
            $this->sock = new Client("wss://{$this->host}:{$this->port}/");
            $this->sock->text(
                json_encode([
                    "name" => $this->clientName,
                    "password" => $this->password,
                ])
            );
        } catch (\Throwable $th) {
            echo $th->getMessage();
            return false;
        }
        return true;
    }

    private function reconnect()
    {
        Await::f2c(function () {
            while (true) {
                $success = $this->createConnection();
                if (!$success) {
                    yield from $this->std->sleep(4);
                    continue;
                }
                break;
            }
        });
    }

    private function handleSentData()
    {
        Await::f2c(function () {
            $this->sock->setTimeout(0.2);
            while (true) {
                if (!$this->sock->isConnected()) {
                    yield from $this->std->sleep(1);
                    return $this->reconnect();
                }
                try {
                    $data = $this->sock->receive();
                    $json = json_decode($data, true);
                    if ($json["from"] === "Server") {
                        var_dump($json["data"]);
                        yield from $this->std->sleep(1);
                        continue;
                    }
                    var_dump($json);
                    if (array_key_exists("api", $json) && $json["api"]) {
                        if ($json["api"] === "response") {
                            $this->requests[$json["id"]]->callback->__invoke(
                                $json["data_type"] === "success"
                                    ? null
                                    : $json["data"]
                            );
                            $this->requests[$json["id"]]->endTask();
                            yield from $this->std->sleep(1);
                            continue;
                        }
                        $data = $this->checkSentData(
                            $json["from"],
                            $json["data_type"],
                            $json["data"]
                        );
                        var_dump($data);
                        if (is_string($data)) {
                            yield from $this->std->sleep(1);
                            continue;
                        }

                        (new RequestReceivedEvent($data, $json["id"]))->call();
                    }
                    $data = $this->checkSentData(
                        $json["from"],
                        $json["data_type"],
                        $json["data"]
                    );
                    var_dump($data);
                    if (is_string($data)) {
                        yield from $this->std->sleep(1);
                        continue;
                    }
                    if ($json["api"] === "response") {
                        $this->requests[$json["id"]]->callback->__invoke($data);
                        $this->requests[$json["id"]]->endTask();
                        yield from $this->std->sleep(1);
                        continue;
                    }
                    if ($this->receivables[$json["data_type"]]->onReceive) {
                        $this->receivables[
                            $json["data_type"]
                        ]->onReceive->__invoke($data);
                    }
                    (new DataReceivedEvent($data))->call();
                    yield from $this->std->sleep(1);
                } catch (\Throwable $th) {
                    // echo "\nline:" .
                    //     $th->getLine() .
                    //     "\nmsg: " .
                    //     $th->getMessage() .
                    //     "\nfile:" .
                    //     $th->getFile() .
                    //     "\ntype: " .
                    //     get_class($th);
                    yield $this->std->sleep(1);
                }
            }
        });
    }

    /**
     * @return object|false it will return false if it has invalid data, it will return a object if everything is valid
     */
    private function checkSentData(
        string $from,
        string $dataType,
        array $data
    ): object|string {
        if (!($receivable = @$this->receivables[$dataType])) {
            return "Array doesnt exist";
        }
        if (in_array($from, $receivable->acceptables)) {
            return $this->checkIfValidClassObject(
                $receivable->namespace,
                $data
            );
        }
        return "Cannot accept client";
    }
    private function checkIfValidClassObject(
        string $class,
        array $obj
    ): string|object {
        $object = new $class();
        $reflectionClass = new \ReflectionClass($class);
        $classProperties = $reflectionClass->getProperties(
            \ReflectionProperty::IS_PUBLIC
        );
        try {
            foreach ($classProperties as $propKey => $property) {
                if (!$property->isPublic()) {
                    continue;
                }
                $propertyType = $property->getType();
                $propertyName = $property->getName();
                if ($propertyType->allowsNull()) {
                    continue;
                }
                if (!@$obj[$propertyName]) {
                    return false;
                }
                if ($propertyType instanceof \ReflectionUnionType) {
                    foreach ($propertyType->getTypes() as $tKey => $type) {
                        if (class_exists($type->getName())) {
                            if (!is_array($obj[$propertyName])) {
                                return false;
                            }
                            $recurved = $this->checkIfValidClassObject(
                                $type->getName(),
                                $obj
                            );
                            if (!$recurved) {
                                return false;
                            }
                            $object->{$propertyName} = $recurved;
                            unset($obj[$propertyName]);
                            continue;
                        }
                        $object->{$propertyName} = $obj[$propertyName];
                        unset($obj[$propertyName]);
                    }
                    continue;
                }
                if (class_exists($propertyType->getName())) {
                    if (!is_array($obj[$propertyName])) {
                        return false;
                    }
                    $recurved = $this->checkIfValidClassObject(
                        $propertyType->getName(),
                        $obj[$propertyName]
                    );
                    if (!$recurved) {
                        return false;
                    }
                    try {
                        $object->{$propertyName} = $recurved;
                    } catch (\Throwable $th) {
                        return false;
                    }
                    unset($obj[$propertyName]);
                    continue;
                }
                $object->{$propertyName} = $obj[$propertyName];
                unset($obj[$propertyName]);
            }
            foreach ($obj as $key => $value) {
                if (!property_exists($class, $key)) {
                    return false;
                }
                $property = $reflectionClass->getProperty($key);
                $propertyType = $property->getType();
                if (!@$key) {
                    return false;
                }
                if ($propertyType instanceof \ReflectionUnionType) {
                    foreach ($propertyType->getTypes() as $tKey => $type) {
                        if (class_exists($type->getName())) {
                            if (!is_array($value)) {
                                return false;
                            }
                            $recurved = $this->checkIfValidClassObject(
                                $type->getName(),
                                $value
                            );
                            if (!$recurved) {
                                return false;
                            }
                            $object->{$key} = $recurved;
                            continue;
                        }
                        $object->{$key} = $value;
                    }
                    continue;
                }
                if (class_exists($propertyType->getName())) {
                    if (!is_array($value)) {
                        return false;
                    }
                    $recurved = $this->checkIfValidClassObject(
                        $propertyType->getName(),
                        $value
                    );
                    $object->{$key} = $recurved;
                    continue;
                }
                $object->{$key} = $value;
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        return $object;
    }
    /**
     * @param string|string[] $to
     */
    public function sendData(object $data, string|array $to): bool
    {
        if (!$this->sock->isConnected()) {
            return false;
        }
        $json = [
            "data" => $data,
            "data_type" => (new \ReflectionClass($data))->getShortName(),
        ];
        $json["to"] = $to;
        $send = json_encode($json);
        try {
            $this->sock->text($send);
            return true;
        } catch (\Throwable $th) {
            return $th->getMessage();
            return false;
        }
    }

    public function sendRequest(
        object $data,
        string|array $to,
        \Closure $responseCallback,
        int $expire = 3
    ) {
        if (!$this->isOn) {
            return false;
        }
        $json = [
            "data" => $data,
            "data_type" => (new \ReflectionClass($data))->getShortName(),
        ];
        $json["to"] = $to;
        $json["api"] = "request";
        $json["id"] = \Ramsey\Uuid\v4();
        $send = json_encode($json);
        try {
            $this->sock->text($send);
            $task = $this->plugin->getScheduler()->scheduleDelayedTask(
                new class ($this->requests, $json["id"]) extends
                    \pocketmine\scheduler\Task
                {
                    public function __construct(
                        private array &$requests,
                        private string $id
                    ) {
                    }
                    public function onRun(): void
                    {
                        if (!$this->requests[$this->id]) {
                            return;
                        }
                        unset($this->requests[$this->id]);
                    }
                },
                20 * $expire
            );
            $this->requests[$json["id"]] = new Request(
                $task,
                $responseCallback
            );
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * @param \HTNProtocol\Streams\JsonStream|string $send String if error, none or a JsonStream object means it's a success
     */
    public function sendResponse(
        string|array $to,
        string $id,
        object|string|null $data = null
    ) {
        if (!$this->sock->isConnected()) {
            return false;
        }
        $toEncode = [
            "data_type" => "success",
            "data" => null,
            "api" => "response",
            "id" => $id,
            "to" => $to,
        ];
        if (is_object($data)) {
            $send = [
                "data" => $data,
                "data_type" => (new \ReflectionClass($data))->getShortName(),
            ];
            $toEncode["data_type"] = $send["data_type"];
            $toEncode["data"] = $send["data"];
        }

        if (is_string($data)) {
            $toEncode["data_type"] = "error";
            $toEncode["data"] = $data;
        }
        $send = json_encode($toEncode);
        try {
            $this->sock->text($send);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
    /**
     * @param string[] $acceptables
     */
    public function registerReceivable(
        string|object $classORobject,
        array $acceptables,
        \Closure $onReceive = null,
        bool $override = false
    ): bool|string {
        if (is_string($classORobject) && !class_exists($classORobject)) {
            return "Argument 1 given a invalid class";
        }
        $shortName = (new \ReflectionClass($classORobject))->getShortName();
        if (array_key_exists($shortName, $this->receivables) && !$override) {
            return "Class already exists";
        }
        $namespace = is_string($classORobject)
            ? $classORobject
            : $classORobject::class;
        $receive = Receivable::create($namespace, $acceptables, $onReceive);
        if (is_string($receive)) {
            return $receive;
        }
        $this->receivables[$shortName] = $receive;
        return true;
    }

    public function closeConnection()
    {
        $this->sock->setTimeout(5);
        $this->sock->close();
    }
    public function getCurrentRequests()
    {
        return $this->requests;
    }
}
