<?php
namespace HTNProtocol;

class DB extends \mysqli
{
    public function __construct()
    {
        parent::__construct(
            "localhost",
            "sqluser",
            "password",
            "ServerDatabase",
            3306
        );
    }
}
