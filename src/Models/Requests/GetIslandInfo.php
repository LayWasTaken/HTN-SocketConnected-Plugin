<?php
namespace HTNProtocol\Models\Requests;

use HTNProtocol\Exceptions\InvalidSetException;

class GetIslandInfo
{
    public string $islandId;
    public string|array $type;
    const MACHINE = "machines";
    const ALL = "all";
    const MEMBERS = "members";
    const SPAWNERS = "spawners";
    const CUBEGENS = "cubegens";
    public function __set($name, $value)
    {
        if ($name === "type") {
            if (is_array($value)) {
                foreach ($value as $key => $type) {
                    $this->checkIfValidType($type)
                        ? 0
                        : throw new InvalidSetException(
                            $name,
                            $type,
                            "Must be a valid island request type"
                        );
                }
            }
            $this->checkIfValidType($type)
                ? 0
                : throw new InvalidSetException(
                    $name,
                    $type,
                    "Must be a valid island request type"
                );
        }
    }
    private function checkIfValidType($value)
    {
        if (
            $value === "machines" ||
            $value === "all" ||
            $value === "members" ||
            $value === "spawners" ||
            $value === "cubegens"
        ) {
            return true;
        }
        return false;
    }
}
