<?php
namespace HTNProtocol\Streams\Extra;

use HTNProtocol\Streams\Exceptions\InvalidSetException;

class KillStat {
    public int $currentPlace;
    public int $kills;
    public function __set($name, $value)
    {
        if($name === "currentPlace") $value <= 0 ? throw new InvalidSetException($name, $value, "Shouldnt be less than 1") : 0;
    }
}