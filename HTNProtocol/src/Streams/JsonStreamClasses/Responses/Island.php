<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Responses;

use HTNProtocol\Streams\Extra\Cubegen;
use HTNProtocol\Streams\Extra\Machine;
use HTNProtocol\Streams\Extra\Member;
use HTNProtocol\Streams\Extra\Spawner;
use HTNProtocol\Streams\Response;

class Island implements Response
{
    /**
     * @var ?Machine[]
     */
    public $machines;
    /**
     * @var ?Cubegen[]
     */
    public $cubegens;
    /**
     * @var ?Member[]
     */
    public $members;
    /**
     * @var ?Spawner[]
     */
    public $spawners;
}