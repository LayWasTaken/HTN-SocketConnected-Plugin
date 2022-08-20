<?php
namespace HTNProtocol\Models\Extra;

class Member
{
    public function __construct(
        public string $member,
        public string $permission
    ) {
    }
}
