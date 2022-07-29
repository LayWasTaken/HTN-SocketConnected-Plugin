<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Others;

use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Extra\Coordinates;

class IslandMachinesEvent {
    public string $islandID;
    public string $machine;
    public string $owner;
    public Coordinates $position;
    public string $event;
    public int $level;
    public function __set($name, $value)
    {   
        if($name === "machine")
            if(!($value === "drill" || $value === "net" || $value === "cannon" || $value === "cubegen")) throw new InvalidSetException($name, $value, "Invalid machine");
        if($name === "event")
            if(!($value === "removed" || $value === "placed")) throw new InvalidSetException($name, $value, "Invalid event");
    }
    public static function create(string $islandID, string $machine, string $owner, Coordinates $position, string $event, int $level){
        $event = new self;
        $event->event = $event;
        $event->islandID = $islandID;
        $event->owner = $owner;
        $event->level = $level;
        $event->position = $position;
        $event->machine = $machine;
        return $event;
    }
}