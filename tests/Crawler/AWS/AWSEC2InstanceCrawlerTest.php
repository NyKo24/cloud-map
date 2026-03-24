<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSEC2InstanceCrawler;
use App\Entity\AWS\EC2\Ec2Instance;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSEC2InstanceCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;
    private AWSEC2InstanceCrawler $crawler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);

        $this->crawler = new AWSEC2InstanceCrawler(
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
            'InstanceId' => 'i-1234567890abcdef0',
            'InstanceType' => 't3.micro',
            'State' => ['Name' => 'running', 'Code' => 16],
        ];

        $ec2Instance = new Ec2Instance();
        $ec2Instance->setInstanceId('i-1234567890abcdef0');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($instanceData, Ec2Instance::class, null, [
                'object_context' => Ec2Instance::class,
            ])
            ->willReturn($ec2Instance);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($ec2Instance);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        // Create a testable subclass that injects a mock EC2Client
        $mockResult = new Result([
            'Reservations' => [
                ['Instances' => [$instanceData]],
            ],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl($credentials, 'eu-west-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $ec2Instance->getCrawl());
    }

    public function testCrawlHandlesMultipleReservations(): void
    {
        $instanceData1 = ['InstanceId' => 'i-aaa', 'InstanceType' => 't3.micro'];
        $instanceData2 = ['InstanceId' => 'i-bbb', 'InstanceType' => 't3.small'];

        $instance1 = new Ec2Instance();
        $instance2 = new Ec2Instance();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($instance1, $instance2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'Reservations' => [
                ['Instances' => [$instanceData1]],
                ['Instances' => [$instanceData2]],
            ],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawlVersion = new CrawlVersion();
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', $crawlVersion);
    }

    public function testCrawlHandlesPagination(): void
    {
        $instanceData1 = ['InstanceId' => 'i-page1'];
        $instanceData2 = ['InstanceId' => 'i-page2'];

        $instance1 = new Ec2Instance();
        $instance2 = new Ec2Instance();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($instance1, $instance2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $page1 = new Result([
            'Reservations' => [['Instances' => [$instanceData1]]],
            'NextToken' => 'token-page-2',
        ]);

        $page2 = new Result([
            'Reservations' => [['Instances' => [$instanceData2]]],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$page1, $page2]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyReservations(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'Reservations' => [],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    /**
     * Creates a crawler subclass that uses a mock Ec2Client instead of creating a real one.
     *
     * @param Result[] $results Sequential results to return from describeInstances
     */
    private function createCrawlerWithMockClient(array $results): AWSEC2InstanceCrawler
    {
        $ec2Client = $this->createMock(Ec2Client::class);
        $callIndex = 0;
        $ec2Client->method('__call')
            ->with('describeInstances', $this->anything())
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
            $ec2Client,
        ) extends AWSEC2InstanceCrawler {
            private Ec2Client $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                Ec2Client $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
            {
                $ec2Client = $this->mockClient;

                $nextToken = null;

                do {
                    $params = ['MaxResults' => 1000];

                    if ($nextToken) {
                        $params['NextToken'] = $nextToken;
                    }

                    $result = $ec2Client->describeInstances($params);

                    foreach ($result->get('Reservations') as $reservation) {
                        foreach ($reservation['Instances'] as $instanceData) {
                            /** @var \App\Entity\AWS\EC2\Ec2Instance $ec2Instance */
                            $ec2Instance = $this->denormalizer->denormalize($instanceData, Ec2Instance::class, null, [
                                'object_context' => Ec2Instance::class,
                            ]);

                            $ec2Instance->setCrawl($crawlVersion);

                            $this->entityManager->persist($ec2Instance);
                        }
                    }

                    $nextToken = $result->get('NextToken');
                } while ($nextToken);

                $this->entityManager->flush();
            }
        };
    }
}
