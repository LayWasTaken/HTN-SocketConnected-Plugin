<?php
namespace HTNProtocol\Models;

class PlayerMessageEvent
{
    public function __construct(public string $player, public string $message)
    {
    }
}
