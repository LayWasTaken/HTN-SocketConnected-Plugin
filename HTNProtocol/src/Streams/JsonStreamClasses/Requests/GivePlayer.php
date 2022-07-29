<?php
namespace HTNProtocol\Streams\JsonStreamClasses\Requests;

use HTNProtocol\Streams\Receivable;
use HTNProtocol\Streams\Request;
class GivePlayer implements Request, Receivable {
    public string $player;
    public string $give;
    public int $amount;
}