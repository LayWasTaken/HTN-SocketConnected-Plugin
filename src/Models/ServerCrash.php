<?php
namespace HTNProtocol\Models;

class ServerCrash
{
    public function __construct(public string $crash_reason)
    {
    }
}
