<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSEcsCrawler;
use App\Entity\AWS\ECS\EcsCluster;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ecs\EcsClient;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSEcsCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testCrawlDenormalizesClusterAndPersists(): void
    {
        $clusterData = [
            'clusterArn' => 'arn:aws:ecs:us-east-1:123456789012:cluster/my-cluster',
            'clusterName' => 'my-cluster',
            'status' => 'ACTIVE',
            'registeredContainerInstancesCount' => 3,
            'runningTasksCount' => 5,
            'activeServicesCount' => 2,
        ];

        $cluster = new EcsCluster();
        $cluster->setClusterName('my-cluster');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($clusterData, EcsCluster::class, null, [
                'object_context' => EcsCluster::class,
            ])
            ->willReturn($cluster);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($cluster);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $crawler = $this->createCrawlerWithMockClient(
            listPages: [['arn:aws:ecs:us-east-1:123456789012:cluster/my-cluster']],
            describeResults: [[$clusterData]],
        );
        $crawler->crawl($credentials, 'us-east-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $cluster->getCrawl());
    }

    public function testCrawlHandlesEmptyClusters(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient(
            listPages: [[]],
            describeResults: [],
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesPagination(): void
    {
        $clusterData1 = ['clusterName' => 'cluster-a', 'status' => 'ACTIVE'];
        $clusterData2 = ['clusterName' => 'cluster-b', 'status' => 'ACTIVE'];

        $cluster1 = new EcsCluster();
        $cluster1->setClusterName('cluster-a');
        $cluster2 = new EcsCluster();
        $cluster2->setClusterName('cluster-b');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($cluster1, $cluster2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient(
            listPages: [['arn:cluster-a'], ['arn:cluster-b']],
            describeResults: [[$clusterData1], [$clusterData2]],
            listNextTokens: ['next-token-1', null],
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    private function createCrawlerWithMockClient(
        array $listPages,
        array $describeResults,
        ?array $listNextTokens = null,
    ): AWSEcsCrawler {
        $ecsClient = $this->createMock(EcsClient::class);
        $listCallIndex = 0;
        $describeCallIndex = 0;

        $ecsClient->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($listPages, $describeResults, $listNextTokens, &$listCallIndex, &$describeCallIndex) {
                if ($method === 'listClusters') {
                    $arns = $listPages[$listCallIndex] ?? [];
                    $nextToken = $listNextTokens[$listCallIndex] ?? null;
                    $listCallIndex++;

                    return new Result([
                        'clusterArns' => $arns,
                        'nextToken' => $nextToken,
                    ]);
                }
                if ($method === 'describeClusters') {
                    $clusters = $describeResults[$describeCallIndex] ?? [];
                    $describeCallIndex++;

                    return new Result([
                        'clusters' => $clusters,
                    ]);
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
            $ecsClient,
        ) extends AWSEcsCrawler {
            private EcsClient $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                EcsClient $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createEcsClient(Credentials $credentials, string $regionName): EcsClient
            {
                return $this->mockClient;
            }
        };
    }
}
