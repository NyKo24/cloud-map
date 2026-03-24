<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSCloudWatchAlarmCrawler;
use App\Entity\AWS\CloudWatch\CloudWatchAlarm;
use App\Entity\CrawlVersion;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Credentials\Credentials;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSCloudWatchAlarmCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testIsGlobalReturnsFalse(): void
    {
        $crawler = new AWSCloudWatchAlarmCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testCrawlDenormalizesAlarmAndPersists(): void
    {
        $alarmData = [
            'AlarmName' => 'my-alarm',
            'AlarmArn' => 'arn:aws:cloudwatch:us-east-1:123456789012:alarm:my-alarm',
            'StateValue' => 'OK',
            'MetricName' => 'CPUUtilization',
            'Namespace' => 'AWS/EC2',
        ];

        $alarm = new CloudWatchAlarm();
        $alarm->setAlarmName('my-alarm');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($alarmData, CloudWatchAlarm::class, null, [
                'object_context' => CloudWatchAlarm::class,
            ])
            ->willReturn($alarm);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($alarm);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $crawler = $this->createCrawlerWithMockClient([[$alarmData]]);
        $crawler->crawl($credentials, 'us-east-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $alarm->getCrawl());
    }

    public function testCrawlHandlesMultipleAlarms(): void
    {
        $alarmData1 = ['AlarmName' => 'alarm-a', 'StateValue' => 'OK'];
        $alarmData2 = ['AlarmName' => 'alarm-b', 'StateValue' => 'ALARM'];

        $alarm1 = new CloudWatchAlarm();
        $alarm1->setAlarmName('alarm-a');
        $alarm2 = new CloudWatchAlarm();
        $alarm2->setAlarmName('alarm-b');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($alarm1, $alarm2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([[$alarmData1, $alarmData2]]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyAlarms(): void
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
        $alarmData1 = ['AlarmName' => 'alarm-page1', 'StateValue' => 'OK'];
        $alarmData2 = ['AlarmName' => 'alarm-page2', 'StateValue' => 'ALARM'];

        $alarm1 = new CloudWatchAlarm();
        $alarm1->setAlarmName('alarm-page1');
        $alarm2 = new CloudWatchAlarm();
        $alarm2->setAlarmName('alarm-page2');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($alarm1, $alarm2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([
            [$alarmData1],  // page 1
            [$alarmData2],  // page 2
        ], ['next-token-1', null]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesNullMetricAlarms(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([null]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    /**
     * @param array $pages Array of alarm data arrays (one per page), or null for missing MetricAlarms key
     * @param array|null $nextTokens Array of NextToken values (one per page), null = no pagination
     */
    private function createCrawlerWithMockClient(
        array $pages,
        ?array $nextTokens = null,
    ): AWSCloudWatchAlarmCrawler {
        $cloudWatchClient = $this->createMock(CloudWatchClient::class);
        $callIndex = 0;

        $cloudWatchClient->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($pages, $nextTokens, &$callIndex) {
                if ($method === 'describeAlarms') {
                    $pageData = $pages[$callIndex] ?? [];
                    $nextToken = $nextTokens[$callIndex] ?? null;
                    $callIndex++;

                    $result = ['NextToken' => $nextToken];
                    if ($pageData !== null) {
                        $result['MetricAlarms'] = $pageData;
                    }

                    return new Result($result);
                }
                return new Result([]);
            });

        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $cloudWatchClient,
        ) extends AWSCloudWatchAlarmCrawler {
            private CloudWatchClient $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                CloudWatchClient $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createCloudWatchClient(Credentials $credentials, string $regionName): CloudWatchClient
            {
                return $this->mockClient;
            }
        };
    }
}
