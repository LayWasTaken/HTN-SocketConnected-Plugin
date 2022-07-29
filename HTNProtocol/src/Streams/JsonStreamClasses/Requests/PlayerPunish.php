<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Requests;

use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Extra\PunishmentTime;
use HTNProtocol\Streams\Receivable;
use HTNProtocol\Streams\Request;

class PlayerPunish implements Request, Receivable {
    const TEMPBAN = "TempBan";
    const BAN = "Ban";
    const WARN = "Warn";
    const MUTE = "Mute";
    const KICK = "Kick";
    public string $type;
    public string $player;
    public string $staff;
    public string $reason;
    public ?PunishmentTime $time;

    public function __set($name, $value)
    {
        if($name === "type"){
            if(!(
                $value === self::TEMPBAN ||
                $value === self::BAN || 
                $value === self::TEMPBAN || 
                $value === self::WARN || 
                $value === self::KICK
            )) throw new InvalidSetException($name, $value, "Should be a valid punishment");
            if(!($this->type === self::TEMPBAN || $this->type === self::MUTE)) 
                if($this->time) throw new InvalidSetException($name, $value, "It shouldnt have time");
        }
        if($name === "time"){
            if(!$this->type) return;
            if(!($this->type === self::TEMPBAN || $this->type === self::MUTE)) throw new InvalidSetException($name, $value, "It shouldnt have time");
        }
    }

}