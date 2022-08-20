<?php
namespace HTNProtocol\Models\Extra;

use HTNProtocol\Exceptions\InvalidSetException;

class Machine
{
    const DRILL = "drill";
    const CANNON = "cannon";
    const NET = "net";
    public string $owner;
    public int $level;
    public Coordinates $location;
    public string $type;
    public function __set($name, $value)
    {
        if ($name === "type") {
            $value === self::NET ||
            $value === self::CANNON ||
            $value === self::DRILL
                ? 0
                : throw new InvalidSetException(
                    $name,
                    $value,
                    "Must be a drill, net, or cannon"
                );
        }
    }
}
