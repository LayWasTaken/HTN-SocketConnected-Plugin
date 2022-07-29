<?php
namespace HTNProtocol\Packets\API\Responses;

use HTNProtocol\Streams\Extra\KillStat;
use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Response;
class Player implements Response{
    public const MEMBER = "member";
    public const DEFENDER = "defender";
    public const GUARDIAN = "guardian";
    public const PRESERVER = "preserver";
    public const CHALLENGER = "challenger";
    public string $id;
    public string $rank;
    public bool $isStaff;
    public bool $isOnline;
    public string $islandId;
    public int $onlineTime;
    public KillStat $killStat;
    public ?Array $friends;
    public string $discord;

    public function __set($name, $value)
    {
        if($name === "rank"){
            if(!(
                $value === "member" ||
                $value === "defender" ||
                $value === "guardian" ||
                $value === "preserver" ||
                $value === "challenger"
            )) throw new InvalidSetException($name, $value, "Should be a valid rank");
        }
        if($name === "friends"){
            foreach ($value as $key => $value) {
                if(!is_string($value)) throw new InvalidSetException($name, $value, "Should be a string");
            }   
        }
    }
}
