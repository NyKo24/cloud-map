<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSVpcCrawler;
use App\Entity\AWS\VPC\Vpc;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSVpcCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testCrawlDenormalizesVpcsAndPersists(): void
    {
        $vpcData = [
            'VpcId' => 'vpc-12345678',
            'CidrBlock' => '10.0.0.0/16',
            'State' => 'available',
            'IsDefault' => true,
        ];

        $vpc = new Vpc();
        $vpc->setVpcId('vpc-12345678');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($vpcData, Vpc::class, null, [
                'object_context' => Vpc::class,
            ])
            ->willReturn($vpc);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($vpc);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $crawler = $this->createCrawlerWithMockClient([$vpcData]);
        $crawler->crawl($credentials, 'eu-west-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $vpc->getCrawl());
    }

    public function testCrawlHandlesMultipleVpcs(): void
    {
        $vpcData1 = ['VpcId' => 'vpc-aaa', 'CidrBlock' => '10.0.0.0/16'];
        $vpcData2 = ['VpcId' => 'vpc-bbb', 'CidrBlock' => '172.16.0.0/16'];

        $vpc1 = new Vpc();
        $vpc1->setVpcId('vpc-aaa');
        $vpc2 = new Vpc();
        $vpc2->setVpcId('vpc-bbb');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($vpc1, $vpc2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $crawler = $this->createCrawlerWithMockClient([$vpcData1, $vpcData2]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', $crawlVersion);

        $this->assertSame($crawlVersion, $vpc1->getCrawl());
        $this->assertSame($crawlVersion, $vpc2->getCrawl());
    }

    public function testCrawlHandlesEmptyVpcs(): void
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

    public function testIsGlobalReturnsFalse(): void
    {
        $crawler = new AWSVpcCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertFalse($crawler->isGlobal());
    }

    /**
     * @param array $vpcs VPC data arrays to return from describeVpcs
     */
    private function createCrawlerWithMockClient(array $vpcs): AWSVpcCrawler
    {
        $ec2Client = $this->createMock(Ec2Client::class);

        $ec2Client->method('__call')
            ->with('describeVpcs', $this->anything())
            ->willReturn(new Result(['Vpcs' => $vpcs]));

        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $ec2Client,
        ) extends AWSVpcCrawler {
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

            protected function createEc2Client(Credentials $credentials, string $regionName): Ec2Client
            {
                return $this->mockClient;
            }
        };
    }
}
