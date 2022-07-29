<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Requests;

use HTNProtocol\Streams\Exceptions\InvalidSetException;
use HTNProtocol\Streams\Receivable;
use HTNProtocol\Streams\Request;

class GetIslandInfo implements Request,Receivable {
    public string $islandId;
    public string|array $type;
    const MACHINE = "machines";
    const ALL = "all";
    const MEMBERS = "members";
    const SPAWNERS = "spawners";
    const CUBEGENS = "cubegens";
    public function __set($name, $value)
    {
        if($name === "type"){
            if(is_array($value)) {
                foreach ($value as $key => $type) {
                    $this->checkIfValidType($type) ? 0 : throw new InvalidSetException($name, $type, "Must be a valid island request type");
                }
            }
            $this->checkIfValidType($type) ? 0 : throw new InvalidSetException($name, $type, "Must be a valid island request type");
        }
    }
    private function checkIfValidType($value){
        if(
        $value === "machines" ||
        $value === "all" ||
        $value === "members" ||
        $value === "spawners" ||
        $value === "cubegens") return true;
        return false;
    }
}