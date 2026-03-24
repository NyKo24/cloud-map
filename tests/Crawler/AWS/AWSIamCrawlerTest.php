<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSIamCrawler;
use App\Entity\AWS\IAM\IamRole;
use App\Entity\AWS\IAM\IamUser;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Iam\IamClient;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSIamCrawlerTest extends TestCase
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
        $crawler = new AWSIamCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->entityManager,
            $this->createMock(SerializerInterface::class),
            $this->denormalizer,
        );

        $this->assertTrue($crawler->isGlobal());
    }

    public function testCrawlDenormalizesRolesAndUsers(): void
    {
        $roleData = [
            'RoleName' => 'my-role',
            'RoleId' => 'AROAEXAMPLE',
            'Arn' => 'arn:aws:iam::123456789012:role/my-role',
            'Path' => '/',
            'CreateDate' => '2024-01-15T10:30:00Z',
        ];

        $userData = [
            'UserName' => 'my-user',
            'UserId' => 'AIDAEXAMPLE',
            'Arn' => 'arn:aws:iam::123456789012:user/my-user',
            'Path' => '/',
            'CreateDate' => '2024-02-01T08:00:00Z',
        ];

        $iamRole = new IamRole();
        $iamRole->setRoleName('my-role');

        $iamUser = new IamUser();
        $iamUser->setUserName('my-user');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(function ($data, $type) use ($roleData, $iamRole, $userData, $iamUser) {
                if ($type === IamRole::class) {
                    $this->assertSame($roleData, $data);
                    return $iamRole;
                }
                if ($type === IamUser::class) {
                    $this->assertSame($userData, $data);
                    return $iamUser;
                }
                $this->fail('Unexpected denormalize call');
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $crawler = $this->createCrawlerWithMockClient([$roleData], [$userData]);
        $crawler->crawl($credentials, 'us-east-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $iamRole->getCrawl());
        $this->assertSame($crawlVersion, $iamUser->getCrawl());
    }

    public function testCrawlHandlesMultipleRolesAndUsers(): void
    {
        $roleData1 = ['RoleName' => 'role-a', 'RoleId' => 'AROA1'];
        $roleData2 = ['RoleName' => 'role-b', 'RoleId' => 'AROA2'];
        $userData1 = ['UserName' => 'user-a', 'UserId' => 'AIDA1'];
        $userData2 = ['UserName' => 'user-b', 'UserId' => 'AIDA2'];

        $role1 = new IamRole();
        $role2 = new IamRole();
        $user1 = new IamUser();
        $user2 = new IamUser();

        $this->denormalizer->expects($this->exactly(4))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($role1, $role2, $user1, $user2);

        $this->entityManager->expects($this->exactly(4))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient(
            [$roleData1, $roleData2],
            [$userData1, $userData2]
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesEmptyResults(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithMockClient([], []);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesPaginatedRoles(): void
    {
        $roleData1 = ['RoleName' => 'role-page1', 'RoleId' => 'AROA1'];
        $roleData2 = ['RoleName' => 'role-page2', 'RoleId' => 'AROA2'];

        $role1 = new IamRole();
        $role2 = new IamRole();

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($role1, $role2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->createCrawlerWithPaginatedRoles(
            [
                ['roles' => [$roleData1], 'isTruncated' => true, 'marker' => 'page2-marker'],
                ['roles' => [$roleData2], 'isTruncated' => false, 'marker' => null],
            ],
            []
        );
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    private function createCrawlerWithMockClient(
        array $roles,
        array $users,
    ): AWSIamCrawler {
        $iamClient = $this->createMock(IamClient::class);

        $iamClient->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($roles, $users) {
                return match ($method) {
                    'listRoles' => new Result([
                        'Roles' => $roles,
                        'IsTruncated' => false,
                    ]),
                    'listUsers' => new Result([
                        'Users' => $users,
                        'IsTruncated' => false,
                    ]),
                    default => new Result([]),
                };
            });

        return $this->buildCrawlerWithClient($iamClient);
    }

    private function createCrawlerWithPaginatedRoles(
        array $rolePages,
        array $users,
    ): AWSIamCrawler {
        $iamClient = $this->createMock(IamClient::class);
        $callIndex = 0;

        $iamClient->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($rolePages, $users, &$callIndex) {
                if ($method === 'listRoles') {
                    $page = $rolePages[$callIndex] ?? ['roles' => [], 'isTruncated' => false, 'marker' => null];
                    $callIndex++;
                    return new Result([
                        'Roles' => $page['roles'],
                        'IsTruncated' => $page['isTruncated'],
                        'Marker' => $page['marker'],
                    ]);
                }
                if ($method === 'listUsers') {
                    return new Result([
                        'Users' => $users,
                        'IsTruncated' => false,
                    ]);
                }
                return new Result([]);
            });

        return $this->buildCrawlerWithClient($iamClient);
    }

    private function buildCrawlerWithClient(IamClient $iamClient): AWSIamCrawler
    {
        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $iamClient,
        ) extends AWSIamCrawler {
            private IamClient $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                IamClient $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createIamClient(Credentials $credentials, string $regionName): IamClient
            {
                return $this->mockClient;
            }
        };
    }
}
