<?php
namespace HTNProtocol\Streams\Extra;

use HTNProtocol\Streams\Exceptions\InvalidSetException;

class Cubegen {
    public string $owner;
    public int $level;
    public string $type;
    public Coordinates $location;
    public function __set($name, $value)
    {
        if($name === 'level') $value > 10 ? throw new InvalidSetException($name, $value, "Shouldnt be more than 10") : 0;
        if($name === 'type'){
            if((
                $value === "PureDiamond" || 
                $value === "PureIron" ||
                $value === "PureGold" ||
                $value === "PureQuartz" ||
                $value === "PureCoal" ||
                $value === "PureEmerald" ||
                $value === "Quartz" ||
                $value === "Diamond" ||
                $value === "Iron" ||
                $value === "Gold" ||
                $value === "Coal" ||
                $value === "Emerald" ||
                $value === "Endstone" ||
                $value === "Nature"
            )) throw new InvalidSetException($name, $value, "Invalid type");
        }
    }
}