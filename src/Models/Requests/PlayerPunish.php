<?php
namespace HTNProtocol\Models\Requests;

use HTNProtocol\Exceptions\InvalidSetException;
use HTNProtocol\Models\Extra\PunishmentTime;

class PlayerPunish
{
    const TEMPBAN = "tempban";
    const BAN = "ban";
    const WARN = "warn";
    const MUTE = "mute";
    const KICK = "kick";
    public string $type;
    public string $player;
    public string $staff;
    public string $reason;
    public ?PunishmentTime $time;

    public function __set($name, $value)
    {
        if ($name === "type") {
            if (
                !(
                    $value === self::TEMPBAN ||
                    $value === self::BAN ||
                    $value === self::TEMPBAN ||
                    $value === self::WARN ||
                    $value === self::KICK
                )
            ) {
                throw new InvalidSetException(
                    $name,
                    $value,
                    "Should be a valid punishment"
                );
            }
            if (
                !($this->type === self::TEMPBAN || $this->type === self::MUTE)
            ) {
                if ($this->time) {
                    throw new InvalidSetException(
                        $name,
                        $value,
                        "It shouldnt have time"
                    );
                }
            }
        }
        if ($name === "time") {
            if (!$this->type) {
                return;
            }
            if (
                !($this->type === self::TEMPBAN || $this->type === self::MUTE)
            ) {
                throw new InvalidSetException(
                    $name,
                    $value,
                    "It shouldnt have time"
                );
            }
        }
    }
}
