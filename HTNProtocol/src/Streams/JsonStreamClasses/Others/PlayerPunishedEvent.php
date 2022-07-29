<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Others;

use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Extra\PunishmentTime;

class PlayerPunishedEvent {
    public string $player;
    public string $type;
    public ?PunishmentTime $time;
    public string $staff;

    public function __set($name, $value)
    {
        if($name === "type")
            if(!($value === "banned" || $value === "kicked" || $value === "mute" || $value === "warned" || $value === "tempban")) throw new InvalidSetException($name, $value, "Invalid type");
    }
}