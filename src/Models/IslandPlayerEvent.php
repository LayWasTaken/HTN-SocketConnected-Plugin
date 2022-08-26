<?php
namespace HTNProtocol\Models;

use HTNProtocol\Exceptions\InvalidSetException;

class IslandPlayersEvents
{
    public const KICKED = "kicked";
    public const BANNED = "banned";
    public const INVITED = "invited";
    public const LEFT = "left";
    public const PROMOTED = "promoted";
    public const DEMOTED = "demoted";
    public string $islandID;
    public string $event;
    public string $player;
    public ?string $member;
    public function __construct(
        string $player,
        string $id,
        string $event,
        string $member = null
    ) {
        $this->islandID = $id;
        $this->event = $event;
        $this->player = $player;
        $this->member = $member;
    }
    public function __set($name, $value)
    {
        if ($name === "type") {
            if (
                !(
                    $value === "kicked" ||
                    $value === "banned" ||
                    $value === "invited" ||
                    $value === "left" ||
                    $value === "promoted" ||
                    $value === "demoted"
                )
            ) {
                throw new InvalidSetException($name, $value, "Invalid type");
            }
        }
    }
}
