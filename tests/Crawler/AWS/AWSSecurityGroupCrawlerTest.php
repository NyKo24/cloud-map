<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSSecurityGroupCrawler;
use App\Entity\AWS\EC2\SecurityGroup;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSSecurityGroupCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testIsNotGlobal(): void
    {
        $crawler = new AWSSecurityGroupCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testCrawlDenormalizesSecurityGroupsAndPersists(): void
    {
        $sgData = [
            'GroupId' => 'sg-12345',
            'GroupName' => 'my-sg',
            'Description' => 'My security group',
            'VpcId' => 'vpc-abc',
            'OwnerId' => '123456789012',
            'IpPermissions' => [
                [
                    'IpProtocol' => 'tcp',
                    'FromPort' => 443,
                    'ToPort' => 443,
                    'IpRanges' => [['CidrIp' => '0.0.0.0/0']],
                    'Ipv6Ranges' => [],
                ],
            ],
            'IpPermissionsEgress' => [
                [
                    'IpProtocol' => '-1',
                    'IpRanges' => [['CidrIp' => '0.0.0.0/0']],
                    'Ipv6Ranges' => [],
                ],
            ],
        ];

        $securityGroup = new SecurityGroup();
        $securityGroup->setGroupId('sg-12345');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($sgData, SecurityGroup::class, null, [
                'object_context' => SecurityGroup::class,
            ])
            ->willReturn($securityGroup);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($securityGroup);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $mockResult = new Result([
            'SecurityGroups' => [$sgData],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl($credentials, 'eu-west-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $securityGroup->getCrawl());
        $this->assertCount(2, $securityGroup->getRules());
        $this->assertCount(1, $securityGroup->getIngressRules());
        $this->assertCount(1, $securityGroup->getEgressRules());
    }

    public function testCrawlHandlesMultipleSecurityGroups(): void
    {
        $sgData1 = ['GroupId' => 'sg-111', 'GroupName' => 'sg1', 'IpPermissions' => [], 'IpPermissionsEgress' => []];
        $sgData2 = ['GroupId' => 'sg-222', 'GroupName' => 'sg2', 'IpPermissions' => [], 'IpPermissionsEgress' => []];

        $sg1 = new SecurityGroup();
        $sg2 = new SecurityGroup();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($sg1, $sg2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'SecurityGroups' => [$sgData1, $sgData2],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawlVersion = new CrawlVersion();
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', $crawlVersion);
    }

    public function testCrawlHandlesPagination(): void
    {
        $sgData1 = ['GroupId' => 'sg-page1', 'IpPermissions' => [], 'IpPermissionsEgress' => []];
        $sgData2 = ['GroupId' => 'sg-page2', 'IpPermissions' => [], 'IpPermissionsEgress' => []];

        $sg1 = new SecurityGroup();
        $sg2 = new SecurityGroup();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($sg1, $sg2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $page1 = new Result([
            'SecurityGroups' => [$sgData1],
            'NextToken' => 'token-page-2',
        ]);

        $page2 = new Result([
            'SecurityGroups' => [$sgData2],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$page1, $page2]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptySecurityGroups(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'SecurityGroups' => [],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlCreatesRulesWithIpv6Ranges(): void
    {
        $sgData = [
            'GroupId' => 'sg-ipv6',
            'GroupName' => 'ipv6-sg',
            'IpPermissions' => [
                [
                    'IpProtocol' => 'tcp',
                    'FromPort' => 80,
                    'ToPort' => 80,
                    'IpRanges' => [],
                    'Ipv6Ranges' => [['CidrIpv6' => '::/0']],
                ],
            ],
            'IpPermissionsEgress' => [],
        ];

        $securityGroup = new SecurityGroup();

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($securityGroup);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $mockResult = new Result([
            'SecurityGroups' => [$sgData],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertCount(1, $securityGroup->getRules());
        $rule = $securityGroup->getRules()->first();
        $this->assertEquals('::/0', $rule->getCidrIpv6());
        $this->assertEquals('ingress', $rule->getDirection());
    }

    public function testCrawlCreatesRulesWithBothIpv4AndIpv6Ranges(): void
    {
        $sgData = [
            'GroupId' => 'sg-mixed',
            'GroupName' => 'mixed-sg',
            'IpPermissions' => [
                [
                    'IpProtocol' => 'tcp',
                    'FromPort' => 443,
                    'ToPort' => 443,
                    'IpRanges' => [
                        ['CidrIp' => '10.0.0.0/8'],
                        ['CidrIp' => '172.16.0.0/12'],
                    ],
                    'Ipv6Ranges' => [
                        ['CidrIpv6' => '::/0'],
                    ],
                ],
            ],
            'IpPermissionsEgress' => [],
        ];

        $securityGroup = new SecurityGroup();

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($securityGroup);

        $mockResult = new Result([
            'SecurityGroups' => [$sgData],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertCount(3, $securityGroup->getRules());
        $this->assertCount(3, $securityGroup->getIngressRules());
        $this->assertCount(0, $securityGroup->getEgressRules());

        $cidrs = [];
        foreach ($securityGroup->getRules() as $rule) {
            $cidrs[] = $rule->getSource();
        }
        $this->assertContains('10.0.0.0/8', $cidrs);
        $this->assertContains('172.16.0.0/12', $cidrs);
        $this->assertContains('::/0', $cidrs);
    }

    public function testCrawlCreatesRuleWithNoIpRanges(): void
    {
        $sgData = [
            'GroupId' => 'sg-norng',
            'GroupName' => 'no-range-sg',
            'IpPermissions' => [
                [
                    'IpProtocol' => '-1',
                    'IpRanges' => [],
                    'Ipv6Ranges' => [],
                ],
            ],
            'IpPermissionsEgress' => [],
        ];

        $securityGroup = new SecurityGroup();

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($securityGroup);

        $mockResult = new Result([
            'SecurityGroups' => [$sgData],
            'NextToken' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([$mockResult]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());

        $this->assertCount(1, $securityGroup->getRules());
        $rule = $securityGroup->getRules()->first();
        $this->assertEquals('-1', $rule->getIpProtocol());
        $this->assertNull($rule->getCidrIp());
        $this->assertNull($rule->getCidrIpv6());
        $this->assertNull($rule->getFromPort());
        $this->assertNull($rule->getToPort());
        $this->assertEquals('ingress', $rule->getDirection());
    }

    /**
     * @param Result[] $results Sequential results to return from describeSecurityGroups
     */
    private function createCrawlerWithMockClient(array $results): AWSSecurityGroupCrawler
    {
        $ec2Client = $this->createMock(Ec2Client::class);
        $callIndex = 0;
        $ec2Client->method('__call')
            ->with('describeSecurityGroups', $this->anything())
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
        ) extends AWSSecurityGroupCrawler {
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
