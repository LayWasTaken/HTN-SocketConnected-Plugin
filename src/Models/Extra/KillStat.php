<?php
namespace HTNProtocol\Models\Extra;

use HTNProtocol\Exceptions\InvalidSetException;

class KillStat
{
    public int $currentPlace;
    public int $kills;
    public function __set($name, $value)
    {
        if ($name === "currentPlace") {
            $value <= 0
                ? throw new InvalidSetException(
                    $name,
                    $value,
                    "Shouldnt be less than 1"
                )
                : 0;
        }
    }
}
