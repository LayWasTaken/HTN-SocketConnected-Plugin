<?php
namespace HTNProtocol\Models;

use HTNProtocol\Exceptions\InvalidSetException;
use HTNProtocol\Models\Extra\PunishmentTime;

class PlayerPunishedEvent
{
    public string $player;
    public string $type;
    public ?PunishmentTime $time;
    public string $staff;
    public string $reason;
    public function __set($name, $value)
    {
        if ($name === "type") {
            if (
                !(
                    $value === "banned" ||
                    $value === "kicked" ||
                    $value === "mute" ||
                    $value === "warned" ||
                    $value === "tempban"
                )
            ) {
                throw new InvalidSetException($name, $value, "Invalid type");
            }
        }
    }
}
