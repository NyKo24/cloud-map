<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSRoute53Crawler;
use App\Entity\AWS\Route53\HostedZone;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\Route53\Route53Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSRoute53CrawlerTest extends TestCase
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
        $crawler = new AWSRoute53Crawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertTrue($crawler->isGlobal());
    }

    public function testCrawlDenormalizesHostedZonesAndPersists(): void
    {
        $zoneData = [
            'Id' => '/hostedzone/Z1234567890',
            'Name' => 'example.com.',
            'CallerReference' => 'ref-001',
            'ResourceRecordSetCount' => 5,
            'Config' => [
                'Comment' => 'My hosted zone',
                'PrivateZone' => false,
            ],
        ];

        $hostedZone = new HostedZone();
        $hostedZone->setHostedZoneId('/hostedzone/Z1234567890');
        $hostedZone->setName('example.com.');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($zoneData, HostedZone::class, null, [
                'object_context' => HostedZone::class,
            ])
            ->willReturn($hostedZone);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($hostedZone);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $crawler = $this->createCrawlerWithMockClient([[$zoneData]]);
        $crawler->crawl($credentials, 'us-east-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $hostedZone->getCrawl());
        $this->assertSame('My hosted zone', $hostedZone->getComment());
        $this->assertFalse($hostedZone->isPrivateZone());
    }

    public function testCrawlSetsConfigFieldsFromNestedData(): void
    {
        $zoneData = [
            'Id' => '/hostedzone/Z999',
            'Name' => 'private.example.com.',
            'CallerReference' => 'ref-private',
            'ResourceRecordSetCount' => 2,
            'Config' => [
                'Comment' => 'Private zone',
                'PrivateZone' => true,
            ],
        ];

        $hostedZone = new HostedZone();

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($hostedZone);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([[$zoneData]]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertSame('Private zone', $hostedZone->getComment());
        $this->assertTrue($hostedZone->isPrivateZone());
    }

    public function testCrawlHandlesZoneWithoutConfigKey(): void
    {
        $zoneData = [
            'Id' => '/hostedzone/Z555',
            'Name' => 'noconfig.example.com.',
            'CallerReference' => 'ref-noconfig',
            'ResourceRecordSetCount' => 1,
        ];

        $hostedZone = new HostedZone();

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($hostedZone);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([[$zoneData]]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertNull($hostedZone->getComment());
        $this->assertNull($hostedZone->isPrivateZone());
    }

    public function testCrawlHandlesConfigWithMissingComment(): void
    {
        $zoneData = [
            'Id' => '/hostedzone/Z777',
            'Name' => 'partial.example.com.',
            'CallerReference' => 'ref-partial',
            'ResourceRecordSetCount' => 3,
            'Config' => [
                'PrivateZone' => true,
            ],
        ];

        $hostedZone = new HostedZone();

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($hostedZone);

        $crawler = $this->createCrawlerWithMockClient([[$zoneData]]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertNull($hostedZone->getComment());
        $this->assertTrue($hostedZone->isPrivateZone());
    }

    public function testCrawlHandlesMultipleHostedZones(): void
    {
        $zoneData1 = [
            'Id' => '/hostedzone/Z001',
            'Name' => 'first.com.',
            'CallerReference' => 'ref-1',
            'ResourceRecordSetCount' => 2,
            'Config' => ['Comment' => 'First', 'PrivateZone' => false],
        ];
        $zoneData2 = [
            'Id' => '/hostedzone/Z002',
            'Name' => 'second.com.',
            'CallerReference' => 'ref-2',
            'ResourceRecordSetCount' => 4,
            'Config' => ['Comment' => 'Second', 'PrivateZone' => true],
        ];

        $zone1 = new HostedZone();
        $zone2 = new HostedZone();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($zone1, $zone2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([[$zoneData1, $zoneData2]]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyHostedZones(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([[]]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesPagination(): void
    {
        $zoneData1 = [
            'Id' => '/hostedzone/Z_PAGE1',
            'Name' => 'page1.com.',
            'CallerReference' => 'ref-p1',
            'ResourceRecordSetCount' => 1,
        ];
        $zoneData2 = [
            'Id' => '/hostedzone/Z_PAGE2',
            'Name' => 'page2.com.',
            'CallerReference' => 'ref-p2',
            'ResourceRecordSetCount' => 3,
        ];

        $zone1 = new HostedZone();
        $zone2 = new HostedZone();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($zone1, $zone2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithPaginatedResults([
            ['zones' => [$zoneData1], 'isTruncated' => true, 'nextMarker' => 'page2-marker'],
            ['zones' => [$zoneData2], 'isTruncated' => false, 'nextMarker' => null],
        ]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlPaginationPassesMarkerToNextRequest(): void
    {
        $zoneData1 = ['Id' => '/hostedzone/Z1', 'Name' => 'a.com.', 'CallerReference' => 'r1', 'ResourceRecordSetCount' => 1];
        $zoneData2 = ['Id' => '/hostedzone/Z2', 'Name' => 'b.com.', 'CallerReference' => 'r2', 'ResourceRecordSetCount' => 1];

        $zone1 = new HostedZone();
        $zone2 = new HostedZone();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($zone1, $zone2);

        $capturedArgs = [];

        $route53Client = $this->createMock(Route53Client::class);
        $callIndex = 0;

        $route53Client->method('__call')
            ->willReturnCallback(function (string $method, array $args) use (&$callIndex, &$capturedArgs) {
                $capturedArgs[] = $args[0] ?? [];
                if ($callIndex === 0) {
                    $callIndex++;
                    return new Result([
                        'HostedZones' => [['Id' => '/hostedzone/Z1', 'Name' => 'a.com.', 'CallerReference' => 'r1', 'ResourceRecordSetCount' => 1]],
                        'IsTruncated' => true,
                        'NextMarker' => 'the-next-marker',
                    ]);
                }
                return new Result([
                    'HostedZones' => [['Id' => '/hostedzone/Z2', 'Name' => 'b.com.', 'CallerReference' => 'r2', 'ResourceRecordSetCount' => 1]],
                    'IsTruncated' => false,
                    'NextMarker' => null,
                ]);
            });

        $crawler = $this->buildCrawlerWithClient($route53Client);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertCount(2, $capturedArgs);
        $this->assertArrayNotHasKey('Marker', $capturedArgs[0]);
        $this->assertSame('the-next-marker', $capturedArgs[1]['Marker']);
    }

    /**
     * @param array<array<array>> $pages Each element is an array of zone data for one page (non-paginated)
     */
    private function createCrawlerWithMockClient(array $pages): AWSRoute53Crawler
    {
        $route53Client = $this->createMock(Route53Client::class);

        $zones = $pages[0] ?? [];

        $route53Client->method('__call')
            ->willReturnCallback(function (string $method) use ($zones) {
                return match ($method) {
                    'listHostedZones' => new Result([
                        'HostedZones' => $zones,
                        'IsTruncated' => false,
                        'NextMarker' => null,
                    ]),
                    default => new Result([]),
                };
            });

        return $this->buildCrawlerWithClient($route53Client);
    }

    private function createCrawlerWithPaginatedResults(array $pages): AWSRoute53Crawler
    {
        $route53Client = $this->createMock(Route53Client::class);
        $callIndex = 0;

        $route53Client->method('__call')
            ->willReturnCallback(function (string $method) use ($pages, &$callIndex) {
                if ($method === 'listHostedZones') {
                    $page = $pages[$callIndex] ?? ['zones' => [], 'isTruncated' => false, 'nextMarker' => null];
                    $callIndex++;
                    return new Result([
                        'HostedZones' => $page['zones'],
                        'IsTruncated' => $page['isTruncated'],
                        'NextMarker' => $page['nextMarker'],
                    ]);
                }
                return new Result([]);
            });

        return $this->buildCrawlerWithClient($route53Client);
    }

    private function buildCrawlerWithClient(Route53Client $route53Client): AWSRoute53Crawler
    {
        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $route53Client,
        ) extends AWSRoute53Crawler {
            private Route53Client $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                Route53Client $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createRoute53Client(Credentials $credentials, string $regionName): Route53Client
            {
                return $this->mockClient;
            }
        };
    }
}
