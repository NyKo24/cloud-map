<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSRdsCrawler;
use App\Entity\AWS\RDS\RdsInstance;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Rds\RdsClient;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSRdsCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;
    private AWSRdsCrawler $crawler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);

        $this->crawler = new AWSRdsCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );
    }

    public function testIsNotGlobal(): void
    {
        $this->assertFalse($this->crawler->isGlobal());
    }

    public function testCrawlDenormalizesInstancesAndPersists(): void
    {
        $instanceData = [
            'DBInstanceIdentifier' => 'my-database',
            'DBInstanceClass' => 'db.t3.micro',
            'Engine' => 'mysql',
            'DBInstanceStatus' => 'available',
        ];

        $rdsInstance = new RdsInstance();
        $rdsInstance->setDbInstanceIdentifier('my-database');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($instanceData, RdsInstance::class, null, [
                'object_context' => RdsInstance::class,
            ])
            ->willReturn($rdsInstance);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($rdsInstance);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $mockResult = new Result([
            'DBInstances' => [$instanceData],
            'Marker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl($credentials, 'eu-west-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $rdsInstance->getCrawl());
    }

    public function testCrawlHandlesMultipleInstances(): void
    {
        $instanceData1 = ['DBInstanceIdentifier' => 'db-aaa', 'Engine' => 'mysql'];
        $instanceData2 = ['DBInstanceIdentifier' => 'db-bbb', 'Engine' => 'postgres'];

        $instance1 = new RdsInstance();
        $instance2 = new RdsInstance();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($instance1, $instance2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'DBInstances' => [$instanceData1, $instanceData2],
            'Marker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawlVersion = new CrawlVersion();
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', $crawlVersion);
    }

    public function testCrawlHandlesPagination(): void
    {
        $instanceData1 = ['DBInstanceIdentifier' => 'db-page1'];
        $instanceData2 = ['DBInstanceIdentifier' => 'db-page2'];

        $instance1 = new RdsInstance();
        $instance2 = new RdsInstance();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($instance1, $instance2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $page1 = new Result([
            'DBInstances' => [$instanceData1],
            'Marker' => 'token-page-2',
        ]);

        $page2 = new Result([
            'DBInstances' => [$instanceData2],
            'Marker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$page1, $page2]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyInstances(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'DBInstances' => [],
            'Marker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    /**
     * Creates a crawler that overrides createRdsClient() to inject a mock,
     * so the real crawl() method is tested.
     *
     * @param Result[] $results Sequential results to return from describeDBInstances
     */
    private function createCrawlerWithMockClient(array $results): AWSRdsCrawler
    {
        $rdsClient = $this->createMock(RdsClient::class);
        $callIndex = 0;
        $rdsClient->method('__call')
            ->with('describeDBInstances', $this->anything())
            ->willReturnCallback(function () use (&$callIndex, $results) {
                return $results[$callIndex++];
            });

        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $rdsClient,
        ) extends AWSRdsCrawler {
            private RdsClient $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                RdsClient $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createRdsClient(Credentials $credentials, string $regionName): RdsClient
            {
                return $this->mockClient;
            }
        };
    }
}
