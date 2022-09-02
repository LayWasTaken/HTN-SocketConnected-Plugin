<?php
namespace HTNProtocol\Models\Responses;

use HTNProtocol\Models\Extra\Cubegen;
use HTNProtocol\Models\Extra\Machine;
use HTNProtocol\Models\Extra\Member;
use HTNProtocol\Models\Extra\Spawner;

class IslandInfo {
    /**
     * @var ?Machine[]
     */
    public array $machines;
    /**
     * @var ?Cubegen[]
     */
    public array $cubegens;
    /**
     * @var Member[]
     */
    public array $members;
    /**
     * @var ?Spawner[]
     */
    public array $spawners;
    public function __construct(Member ...$members) {
        $this->members = $members;
    }

    public function setMachines(Machine ...$machines) {
        $this->machines = $machines;
        return $this;
    }

    public function setSpawners(Spawner ...$spawners) {
        $this->spawners = $spawners;
        return $this;
    }

    public function setCubegens(Cubegen ...$cubegens) {
        $this->cubegens = $cubegens;
        return $this;
    }
}
