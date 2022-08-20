<?php
namespace HTNProtocol\Events;

use pocketmine\event\Event;

class DataReceivedEvent extends Event {
    public function __construct(private object $data) { }

    public function getData(){ return $this->data; }
}