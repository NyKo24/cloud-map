<?php

namespace App\Search;

class S3BucketListSearch
{
    public ?string $name = null;
    public ?string $region = null;
    public ?string $versioningStatus = null;
    public ?bool $encryptionEnabled = null;
    public ?int $userId = null;
}
