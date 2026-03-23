<?php

namespace App\Search;

use App\Enum\AWS\Lambda\LambdaRuntime;
use App\Enum\AWS\Lambda\LambdaState;
use App\Enum\AWS\Lambda\PackageType;

class LambdaFunctionListSearch
{
    public ?string $functionName = null;
    public ?string $functionArn = null;
    public ?LambdaRuntime $runtime = null;
    public ?LambdaState $state = null;
    public ?PackageType $packageType = null;
    public ?int $userId = null;
}