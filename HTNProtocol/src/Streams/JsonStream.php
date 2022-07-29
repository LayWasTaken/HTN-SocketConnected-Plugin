<?php
namespace HTNProtocol\Streams;

class JsonStream {
    protected string $data_type;
    protected object $data;
    protected ?string $api;
    protected ?int $id;
    protected function __construct(object $data, string $data_type, string $api = null, string $id = null){
        $this->data = $data;
        $this->data_type = $data_type;
        if($api === "request" || $api === "response") {
            $this->api = $api;
            $this->id = $id ? $id : mt_rand(0, 10000);
        }
    }   
    public function getDataType():string{ return $this->data_type; }
    public function getData():object{ return $this->data; }
    public function getApiType():string{ return $this->api; }
    public function getId():int{ return $this->id; }
}