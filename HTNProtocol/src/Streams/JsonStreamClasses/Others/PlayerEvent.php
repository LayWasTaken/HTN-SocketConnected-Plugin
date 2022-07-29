<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Others;

use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Extra\Coordinates;

class PlayerEvent {
    public string $player;
    public string $event;
    public ?Coordinates $position;
    public ?string $lastDamager;
    public ?string $cause;
    public ?bool $isNew;

    public function __set($name, $value)
    {
        if($name === "event")
            if(!($value === "dies" || $value === "join" || $value === "quit")) throw new InvalidSetException($name, $value, "Invalid type");
    }
}