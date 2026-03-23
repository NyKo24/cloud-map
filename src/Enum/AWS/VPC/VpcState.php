<?php

namespace App\Enum\AWS\VPC;

enum VpcState: string
{
    case AVAILABLE = 'available';
    case PENDING = 'pending';
}
