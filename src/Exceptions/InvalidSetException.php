<?php
namespace HTNProtocol\Exceptions;

use Exception;

class InvalidSetException extends Exception
{
    public $property;
    public $value;
    public string $reason;
    public function __construct($property, $value, string $reason)
    {
        $this->value = $value;
        $this->property = $property;
        $this->reason = $reason;
        parent::__construct("Invalid $property, Reason: $reason");
    }
}
