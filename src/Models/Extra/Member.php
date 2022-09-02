<?php
namespace HTNProtocol\Models\Extra;

class Member {
    public const CO_OWNER = 'co-owner';
    public const OWNER = 'owner';
    public function __construct(
        public string $name,
        public string $xuid,
        public string $permission
    ) {
    }
}
