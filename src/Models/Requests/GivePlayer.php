<?php
namespace HTNProtocol\Models\Requests;

class GivePlayer {
    public string $player;
    /**
     * @var string $give 'money' | item names
     */
    public string $give;
    public int $amount;
}
