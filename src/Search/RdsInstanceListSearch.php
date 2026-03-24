<?php

namespace App\Search;

class RdsInstanceListSearch
{
    public ?string $dbInstanceIdentifier = null;
    public ?string $engine = null;
    public ?string $dbInstanceStatus = null;
    public ?string $dbInstanceClass = null;
    public ?int $userId = null;
}
