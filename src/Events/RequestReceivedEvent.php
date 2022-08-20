<?php
namespace HTNProtocol\Events;
class RequestReceivedEvent extends DataReceivedEvent {
    public function __construct(object $data, private $id) {
        parent::__construct($data);
    }
    public function getId(){ return $this->id; }
}