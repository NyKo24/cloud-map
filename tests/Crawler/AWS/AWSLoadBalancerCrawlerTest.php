<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSLoadBalancerCrawler;
use App\Entity\AWS\ELB\LoadBalancer;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSLoadBalancerCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testCrawlDenormalizesLoadBalancersAndPersists(): void
    {
        $lbData = [
            'LoadBalancerArn' => 'arn:aws:elasticloadbalancing:eu-west-1:123:loadbalancer/app/my-alb/abc123',
            'LoadBalancerName' => 'my-alb',
            'Type' => 'application',
            'Scheme' => 'internet-facing',
        ];

        $loadBalancer = new LoadBalancer();
        $loadBalancer->setLoadBalancerName('my-alb');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($lbData, LoadBalancer::class, null, [
                'object_context' => LoadBalancer::class,
            ])
            ->willReturn($loadBalancer);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($loadBalancer);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $mockResult = new Result([
            'LoadBalancers' => [$lbData],
            'NextMarker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl($credentials, 'eu-west-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $loadBalancer->getCrawl());
    }

    public function testCrawlHandlesMultipleLoadBalancers(): void
    {
        $lbData1 = ['LoadBalancerName' => 'alb-1', 'Type' => 'application'];
        $lbData2 = ['LoadBalancerName' => 'nlb-1', 'Type' => 'network'];

        $lb1 = new LoadBalancer();
        $lb2 = new LoadBalancer();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($lb1, $lb2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'LoadBalancers' => [$lbData1, $lbData2],
            'NextMarker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawlVersion = new CrawlVersion();
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', $crawlVersion);
    }

    public function testCrawlHandlesPagination(): void
    {
        $lbData1 = ['LoadBalancerName' => 'lb-page1'];
        $lbData2 = ['LoadBalancerName' => 'lb-page2'];

        $lb1 = new LoadBalancer();
        $lb2 = new LoadBalancer();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($lb1, $lb2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $page1 = new Result([
            'LoadBalancers' => [$lbData1],
            'NextMarker' => 'token-page-2',
        ]);

        $page2 = new Result([
            'LoadBalancers' => [$lbData2],
            'NextMarker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$page1, $page2]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyLoadBalancers(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'LoadBalancers' => [],
            'NextMarker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    /**
     * @param Result[] $results Sequential results to return from describeLoadBalancers
     */
    private function createCrawlerWithMockClient(array $results): AWSLoadBalancerCrawler
    {
        $elbClient = $this->createMock(ElasticLoadBalancingV2Client::class);
        $callIndex = 0;
        $elbClient->method('__call')
            ->with('describeLoadBalancers', $this->anything())
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
            $elbClient,
        ) extends AWSLoadBalancerCrawler {
            private ElasticLoadBalancingV2Client $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                ElasticLoadBalancingV2Client $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createElbClient(Credentials $credentials, string $regionName): ElasticLoadBalancingV2Client
            {
                return $this->mockClient;
            }
        };
    }
}
