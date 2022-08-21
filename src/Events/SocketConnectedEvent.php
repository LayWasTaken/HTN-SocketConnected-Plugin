<?php
namespace HTNProtocol\Events;

use HTNProtocol\ClientSocket;

class SocketConnectedEvent
{
    public function __construct(private ClientSocket $socket)
    {
    }

    public function getSocket()
    {
        return $this->socket;
    }
}
