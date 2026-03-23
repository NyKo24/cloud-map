<?php

namespace App\Crawler\AWS;

use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('crawler.aws')]
interface AWSCrawlerInterface
{
    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void;
}
