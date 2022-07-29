<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Requests;

use HTNProtocol\Streams\Receivable;
use HTNProtocol\Streams\Request;

class GetPlayerInfo implements Request, Receivable {
    public string $player;
}