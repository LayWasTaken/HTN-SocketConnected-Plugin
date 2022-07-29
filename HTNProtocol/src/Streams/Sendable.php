<?php
namespace HTNProtocol\Streams;

use InvalidArgumentException;

class Sendable extends JsonStream {
    public ?int $id;
    public ?string $type;
    public string|array $to;

    public function __construct(string|array $to, object $data, ?string $type = false, ?int $id = false){
        parent::__construct($data);
        if($type){
            $this->type = ($type === "request" || $type === "response") ? $type : throw new InvalidArgumentException("type must be a request or a response");
            
            $this->to = $to;
            $this->id = $id ? $id : mt_rand(10, 100000);
        }
    }
}

