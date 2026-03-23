<?php

namespace App\Enum\AWS\Lambda;

enum LambdaState: string
{
    case PENDING = 'Pending';
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';
    case FAILED = 'Failed';
}