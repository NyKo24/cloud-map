<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\S3\S3Bucket;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class AWSS3BucketCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return true;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $s3Client = $this->createS3Client($credentials, $regionName);

        $result = $s3Client->listBuckets();

        foreach ($result->get('Buckets') ?? [] as $bucketData) {
            /** @var S3Bucket $s3Bucket */
            $s3Bucket = $this->denormalizer->denormalize($bucketData, S3Bucket::class, null, [
                'object_context' => S3Bucket::class,
            ]);

            $s3Bucket->setCrawl($crawlVersion);

            $this->fetchBucketLocation($s3Client, $s3Bucket);
            $this->fetchBucketEncryption($s3Client, $s3Bucket);
            $this->fetchBucketVersioning($s3Client, $s3Bucket);

            $this->entityManager->persist($s3Bucket);
        }

        $this->entityManager->flush();
    }

    private function fetchBucketLocation(S3Client $s3Client, S3Bucket $s3Bucket): void
    {
        try {
            $location = $s3Client->getBucketLocation([
                'Bucket' => $s3Bucket->getName(),
            ]);

            $region = $location->get('LocationConstraint');
            // Empty string means us-east-1
            $s3Bucket->setRegion($region ?: 'us-east-1');
        } catch (\Exception $e) {
            // Access might be denied, continue crawling
        }
    }

    private function fetchBucketEncryption(S3Client $s3Client, S3Bucket $s3Bucket): void
    {
        try {
            $s3Client->getBucketEncryption([
                'Bucket' => $s3Bucket->getName(),
            ]);

            $s3Bucket->setEncryptionEnabled(true);
        } catch (\Exception $e) {
            // ServerSideEncryptionConfigurationNotFoundError means no encryption
            $s3Bucket->setEncryptionEnabled(false);
        }
    }

    private function fetchBucketVersioning(S3Client $s3Client, S3Bucket $s3Bucket): void
    {
        try {
            $versioning = $s3Client->getBucketVersioning([
                'Bucket' => $s3Bucket->getName(),
            ]);

            $s3Bucket->setVersioningStatus($versioning->get('Status') ?? 'Disabled');
        } catch (\Exception $e) {
            // Access might be denied, continue crawling
        }
    }

    protected function createS3Client(Credentials $credentials, string $regionName): S3Client
    {
        return new S3Client([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);
    }
}
