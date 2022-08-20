<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Others;

use HTNProtocol\Streams\Extra\PlayerDeath;
use InvalidArgumentException;

class PlayerEvent {
    public string $player;
    public string $event;
    public bool $isNew = false;
    public ?PlayerDeath $DeathInfo = null;
    
    public function __construct(string $event, string $name, object $additional = null) {
        if(!($event === "dies" || $event === "join" || $event === "quit")) throw new InvalidArgumentException("Invalid event");
        $this->DeathInfo = $additional;
        $this->event = $event;
        $this->player = $name;
    }

    public function setIsNew(bool $set){
        $this->isNew = $set;
        return $this;
    }
}