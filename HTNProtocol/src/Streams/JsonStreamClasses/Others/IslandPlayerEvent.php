<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Others;

use HTNProtocol\Streams\Exceptions\InvalidSetException;

class IslandPlayersEvents {
    public string $islandID;
    public string $event;
    public string $player;
    public ?string $punisher;
    public function __set($name, $value)
    {
        if($name === "type")
            if(!(
                $value === "kicked" || 
                $value === "banned" || 
                $value === "invited" ||
                $value === "left" ||
                $value === "promoted" ||
                $value === "demoted" 
            )) throw new InvalidSetException($name, $value, "Invalid type");        
    }
}