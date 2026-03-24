<?php

namespace App\Search;

class Ec2InstanceListSearch
{
    public ?string $instanceId = null;
    public ?string $instanceType = null;
    public ?string $state = null;
    public ?string $vpcId = null;
    public ?string $architecture = null;
    public ?int $userId = null;
}
