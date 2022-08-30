<?php
namespace HTNProtocol;

class Receivable {
    public string $namespace;
    public ?\Closure $onReceive;
    public array $acceptables;
    /**
     * @param string[] $acceptables
     */
    public static function create(
        string $namespace,
        array $acceptables,
        ?\Closure $onReceive
    ): Receivable|string {
        foreach ($acceptables as $key => $value) {
            if (!is_string($value)) {
                return "$value isnt a string";
            }
        }
        $receivable = new self();
        $receivable->namespace = $namespace;
        $receivable->onReceive = $onReceive;
        $receivable->acceptables = $acceptables;
        return $receivable;
    }
}
