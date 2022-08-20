<?php
namespace HTNProtocol\Models\Extra;

use HTNProtocol\Exceptions\InvalidSetException;

class PunishmentTime
{
    public int $days;
    public int $hours;
    public int $minutes;
    public function __set($name, $value)
    {
        if ($name === "hours" || $name === "minutes") {
            if ($value >= 60) {
                throw new InvalidSetException(
                    $name,
                    $value,
                    "Must be less than 60"
                );
            }
        }
    }
}
