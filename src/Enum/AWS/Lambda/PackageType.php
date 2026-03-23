<?php

namespace App\Enum\AWS\Lambda;

enum PackageType: string
{
    case ZIP = 'Zip';
    case IMAGE = 'Image';
}