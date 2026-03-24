<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSS3BucketCrawler;
use App\Entity\AWS\S3\S3Bucket;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSS3BucketCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testIsGlobalReturnsTrue(): void
    {
        $crawler = new AWSS3BucketCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertTrue($crawler->isGlobal());
    }

    public function testCrawlDenormalizesBucketsAndPersists(): void
    {
        $bucketData = [
            'Name' => 'my-test-bucket',
            'CreationDate' => '2024-01-15T10:30:00Z',
        ];

        $s3Bucket = new S3Bucket();
        $s3Bucket->setName('my-test-bucket');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($bucketData, S3Bucket::class, null, [
                'object_context' => S3Bucket::class,
            ])
            ->willReturn($s3Bucket);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($s3Bucket);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $crawler = $this->createCrawlerWithMockClient([$bucketData]);
        $crawler->crawl($credentials, 'us-east-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $s3Bucket->getCrawl());
    }

    public function testCrawlHandlesMultipleBuckets(): void
    {
        $bucketData1 = ['Name' => 'bucket-aaa', 'CreationDate' => '2024-01-01'];
        $bucketData2 = ['Name' => 'bucket-bbb', 'CreationDate' => '2024-02-01'];

        $bucket1 = new S3Bucket();
        $bucket1->setName('bucket-aaa');
        $bucket2 = new S3Bucket();
        $bucket2->setName('bucket-bbb');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($bucket1, $bucket2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([$bucketData1, $bucketData2]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyBuckets(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlFetchesBucketLocationEncryptionAndVersioning(): void
    {
        $bucketData = ['Name' => 'my-bucket', 'CreationDate' => '2024-01-01'];

        $s3Bucket = new S3Bucket();
        $s3Bucket->setName('my-bucket');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($s3Bucket);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient(
            [$bucketData],
            'eu-west-1',
            null,
            'Enabled'
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertSame('eu-west-1', $s3Bucket->getRegion());
        $this->assertTrue($s3Bucket->isEncryptionEnabled());
        $this->assertSame('Enabled', $s3Bucket->getVersioningStatus());
    }

    public function testCrawlHandlesEmptyLocationAsUsEast1(): void
    {
        $bucketData = ['Name' => 'us-bucket', 'CreationDate' => '2024-01-01'];

        $s3Bucket = new S3Bucket();
        $s3Bucket->setName('us-bucket');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($s3Bucket);

        $crawler = $this->createCrawlerWithMockClient(
            [$bucketData],
            '',
            null,
            'Suspended'
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertSame('us-east-1', $s3Bucket->getRegion());
    }

    public function testCrawlSetsEncryptionNullOnAccessDenied(): void
    {
        $bucketData = ['Name' => 'denied-bucket', 'CreationDate' => '2024-01-01'];

        $s3Bucket = new S3Bucket();
        $s3Bucket->setName('denied-bucket');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($s3Bucket);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Failed to fetch S3 bucket encryption'),
                $this->callback(fn(array $ctx) => $ctx['bucket'] === 'denied-bucket' && $ctx['error'] === 'AccessDenied')
            );

        $crawler = $this->createCrawlerWithMockClient(
            [$bucketData],
            'eu-west-1',
            'AccessDenied',
            'Enabled',
            $logger,
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertNull($s3Bucket->isEncryptionEnabled());
    }

    public function testCrawlSetsEncryptionFalseOnNotFoundError(): void
    {
        $bucketData = ['Name' => 'no-encryption-bucket', 'CreationDate' => '2024-01-01'];

        $s3Bucket = new S3Bucket();
        $s3Bucket->setName('no-encryption-bucket');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($s3Bucket);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient(
            [$bucketData],
            'eu-west-1',
            'ServerSideEncryptionConfigurationNotFoundError',
            'Enabled',
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertFalse($s3Bucket->isEncryptionEnabled());
    }

    /**
     * @param array $buckets
     * @param string|null $location
     * @param string|null $encryptionErrorCode null means encryption exists, string is the S3Exception error code to throw
     * @param string|null $versioningStatus
     * @param LoggerInterface|null $logger
     */
    private function createCrawlerWithMockClient(
        array $buckets,
        ?string $location = 'eu-west-1',
        ?string $encryptionErrorCode = null,
        ?string $versioningStatus = 'Enabled',
        ?LoggerInterface $logger = null,
    ): AWSS3BucketCrawler {
        $s3Client = $this->createMock(S3Client::class);

        $s3Client->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($buckets, $location, $encryptionErrorCode, $versioningStatus) {
                return match ($method) {
                    'listBuckets' => new Result(['Buckets' => $buckets]),
                    'getBucketLocation' => new Result(['LocationConstraint' => $location]),
                    'getBucketEncryption' => $encryptionErrorCode !== null
                        ? throw new \Aws\S3\Exception\S3Exception(
                            $encryptionErrorCode,
                            $this->createMock(\Aws\CommandInterface::class),
                            ['code' => $encryptionErrorCode],
                        )
                        : new Result(['ServerSideEncryptionConfiguration' => []]),
                    'getBucketVersioning' => new Result(['Status' => $versioningStatus]),
                    default => new Result([]),
                };
            });

        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $s3Client,
            $logger,
        ) extends AWSS3BucketCrawler {
            private S3Client $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                S3Client $mockClient,
                ?LoggerInterface $logger = null,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer, $logger);
                $this->mockClient = $mockClient;
            }

            protected function createS3Client(Credentials $credentials, string $regionName): S3Client
            {
                return $this->mockClient;
            }
        };
    }
}
