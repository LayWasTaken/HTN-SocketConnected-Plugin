<?php
namespace HTNProtocol\Events;

use HTNProtocol\Packets\SendablePacket;
use HTNProtocol\Packets\SentDataPacket;
use pocketmine\event\Event;

class PacketReceiveEvent extends Event
{
    private SentDataPacket $packet;
    public function __construct(SentDataPacket $packet) {
        $this->packet = $packet;
    }
    public function getPacketData()
    {
        return $this->packet;
    }
}
