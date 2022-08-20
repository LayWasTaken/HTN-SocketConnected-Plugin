<?php
namespace HTNProtocol\Models\Extra;

class PlayerDeath
{
    public function __construct(
        public Coordinates $location,
        public string $lastDamager,
        public string $cause
    ) {
    }
}
