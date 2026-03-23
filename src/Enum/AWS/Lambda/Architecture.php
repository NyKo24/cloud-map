<?php

namespace App\Enum\AWS\Lambda;

enum Architecture: string
{
    case X86_64 = 'x86_64';
    case ARM64 = 'arm64';
}