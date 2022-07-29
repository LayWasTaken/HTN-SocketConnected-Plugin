<?php
namespace HTNProtocol\Streams\Extra;

use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Extra\Coordinates;

class Machine {
    const DRILL = "drill";
    const CANNON = "cannon";
    const NET = "net";
    public string $owner;
    public int $level;
    public Coordinates $location;
    public string $type;
    public function __set($name, $value)
    {
        if($name === "type"){
            ($value === self::NET || $value === self::CANNON || $value === self::DRILL) ? 0 : throw new InvalidSetException($name, $value, "Must be a drill, net, or cannon");
        }
    }

}