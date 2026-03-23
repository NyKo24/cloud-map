<?php

namespace App\Enum\AWS;

enum Tenancy: string
{
    case DEDICATED = 'dedicated';
    case DEFAULT = 'default';
    case HOST = 'host';
}
