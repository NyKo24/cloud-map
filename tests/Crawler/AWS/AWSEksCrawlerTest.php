<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSEksCrawler;
use App\Entity\AWS\EKS\EksCluster;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\EKS\EKSClient;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSEksCrawlerTest extends TestCase
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
        $crawler = new AWSEksCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testCrawlDenormalizesClusterAndPersists(): void
    {
        $clusterData = [
            'name' => 'my-eks-cluster',
            'arn' => 'arn:aws:eks:us-east-1:123456789012:cluster/my-eks-cluster',
            'version' => '1.28',
            'status' => 'ACTIVE',
            'platformVersion' => 'eks.5',
            'endpoint' => 'https://example.eks.amazonaws.com',
            'roleArn' => 'arn:aws:iam::123456789012:role/eks-role',
        ];

        $cluster = new EksCluster();
        $cluster->setName('my-eks-cluster');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($clusterData, EksCluster::class, null, [
                'object_context' => EksCluster::class,
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
            listPages: [['my-eks-cluster']],
            describeResults: [$clusterData],
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
        $clusterData1 = ['name' => 'cluster-a', 'status' => 'ACTIVE'];
        $clusterData2 = ['name' => 'cluster-b', 'status' => 'ACTIVE'];

        $cluster1 = new EksCluster();
        $cluster1->setName('cluster-a');
        $cluster2 = new EksCluster();
        $cluster2->setName('cluster-b');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($cluster1, $cluster2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient(
            listPages: [['cluster-a'], ['cluster-b']],
            describeResults: [$clusterData1, $clusterData2],
            listNextTokens: ['next-token-1', null],
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    private function createCrawlerWithMockClient(
        array $listPages,
        array $describeResults,
        ?array $listNextTokens = null,
    ): AWSEksCrawler {
        $eksClient = $this->createMock(EKSClient::class);
        $listCallIndex = 0;
        $describeCallIndex = 0;

        $eksClient->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($listPages, $describeResults, $listNextTokens, &$listCallIndex, &$describeCallIndex) {
                if ($method === 'listClusters') {
                    $names = $listPages[$listCallIndex] ?? [];
                    $nextToken = $listNextTokens[$listCallIndex] ?? null;
                    $listCallIndex++;

                    return new Result([
                        'clusters' => $names,
                        'nextToken' => $nextToken,
                    ]);
                }
                if ($method === 'describeCluster') {
                    $data = $describeResults[$describeCallIndex] ?? null;
                    $describeCallIndex++;

                    return new Result([
                        'cluster' => $data,
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
            $eksClient,
        ) extends AWSEksCrawler {
            private EKSClient $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                EKSClient $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
            {
                $eksClient = $this->mockClient;
                $nextToken = null;

                do {
                    $params = [];
                    if ($nextToken) {
                        $params['nextToken'] = $nextToken;
                    }

                    $result = $eksClient->listClusters($params);
                    $clusterNames = $result->get('clusters') ?? [];

                    foreach ($clusterNames as $clusterName) {
                        $described = $eksClient->describeCluster(['name' => $clusterName]);
                        $clusterData = $described->get('cluster');

                        if ($clusterData) {
                            $cluster = $this->denormalizer->denormalize($clusterData, EksCluster::class, null, [
                                'object_context' => EksCluster::class,
                            ]);
                            $cluster->setCrawl($crawlVersion);
                            $this->entityManager->persist($cluster);
                        }
                    }

                    $nextToken = $result->get('nextToken');
                } while ($nextToken);

                $this->entityManager->flush();
            }
        };
    }
}
