<?php

namespace App\Tests\Entity\AWS\S3;

use App\Entity\AWS\S3\S3Bucket;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class S3BucketTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $bucket = new S3Bucket();

        $bucket->setName('my-bucket');
        $this->assertSame('my-bucket', $bucket->getName());

        $bucket->setRegion('eu-west-1');
        $this->assertSame('eu-west-1', $bucket->getRegion());

        $bucket->setEncryptionEnabled(true);
        $this->assertTrue($bucket->isEncryptionEnabled());

        $bucket->setVersioningStatus('Enabled');
        $this->assertSame('Enabled', $bucket->getVersioningStatus());

        $date = new \DateTimeImmutable('2024-01-15');
        $bucket->setCreationDate($date);
        $this->assertSame($date, $bucket->getCreationDate());
    }

    public function testCrawlVersionRelationship(): void
    {
        $bucket = new S3Bucket();
        $crawlVersion = new CrawlVersion();

        $bucket->setCrawl($crawlVersion);
        $this->assertSame($crawlVersion, $bucket->getCrawl());
    }

    public function testCrawlVersionAddS3Bucket(): void
    {
        $crawlVersion = new CrawlVersion();
        $bucket = new S3Bucket();

        $crawlVersion->addS3Bucket($bucket);

        $this->assertCount(1, $crawlVersion->getS3Buckets());
        $this->assertSame($crawlVersion, $bucket->getCrawl());
    }

    public function testCrawlVersionAddS3BucketDoesNotDuplicate(): void
    {
        $crawlVersion = new CrawlVersion();
        $bucket = new S3Bucket();

        $crawlVersion->addS3Bucket($bucket);
        $crawlVersion->addS3Bucket($bucket);

        $this->assertCount(1, $crawlVersion->getS3Buckets());
    }

    public function testCrawlVersionRemoveS3Bucket(): void
    {
        $crawlVersion = new CrawlVersion();
        $bucket = new S3Bucket();

        $crawlVersion->addS3Bucket($bucket);
        $this->assertCount(1, $crawlVersion->getS3Buckets());

        $crawlVersion->removeS3Bucket($bucket);
        $this->assertCount(0, $crawlVersion->getS3Buckets());
        $this->assertNull($bucket->getCrawl());
    }

    public function testDefaultValues(): void
    {
        $bucket = new S3Bucket();

        $this->assertNull($bucket->getId());
        $this->assertNull($bucket->getName());
        $this->assertNull($bucket->getRegion());
        $this->assertNull($bucket->isEncryptionEnabled());
        $this->assertNull($bucket->getVersioningStatus());
        $this->assertNull($bucket->getCreationDate());
        $this->assertNull($bucket->getCrawl());
    }
}
