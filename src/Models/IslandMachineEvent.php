<?php
namespace HTNProtocol\Models;

use HTNProtocol\Models\Extra\Coordinates;

class IslandMachinesEvent
{
    public const NET = "net";
    public const DRILL = "drill";
    public const CANNON = "cannon";
    public const CUBEGEN = "cubegen";

    public const PLACE_EVENT = "placed";
    public const REMOVE_EVENT = "event";
    public function __construct(
        public string $islandID,
        public string $machine,
        public string $owner,
        public Coordinates $position,
        public string $event,
        public int $level
    ) {
    }
    // public function __set($name, $value)
    // {
    //     if($name === "machine")
    //         if(!($value === "drill" || $value === "net" || $value === "cannon" || $value === "cubegen")) throw new InvalidSetException($name, $value, "Invalid machine");
    //     if($name === "event")
    //         if(!($value === "removed" || $value === "placed")) throw new InvalidSetException($name, $value, "Invalid event");
    // }
}
