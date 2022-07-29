<?php
namespace HTNProtocol;

use HTNProtocol\Streams\Receivable;
use pocketmine\utils\Config;
use ReflectionClass;

final class ReceivableConfig extends Config {

    public function __construct(private \pocketmine\plugin\Plugin $plugin)
    {
        parent::__construct($plugin->getDataFolder() . "SocketConfig.json", Config::JSON);
    }
    /**
     * @return true When everything is done without errors
     */
    public function registerReceivableClass(string|Receivable $class, array|string $clients = null):bool
    {
        try {
            $refClass = new ReflectionClass($class);
            $data = $this->getAll();
            $array = [
                $refClass->getShortName() => [
                    "namespace" => $class,
                    "acceptable_clients" => $clients
                ]
            ];
            $data = array_merge($data, $array);
            $this->setAll($data);
            $this->save();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}