<?php

namespace App\Tests\Repository\AWS\EC2;

use App\Repository\AWS\EC2\SecurityGroupRepository;
use App\Search\SecurityGroupListSearch;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class SecurityGroupRepositoryTest extends TestCase
{
    private SecurityGroupRepository $repository;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../../../../src/Entity'],
            true
        );
        $connection = DriverManager::getConnection(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            $config
        );
        $em = new EntityManager($connection, $config);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);
        $registry->method('getManager')->willReturn($em);

        $this->repository = new SecurityGroupRepository($registry);
    }

    public function testListSecurityGroupsForUserIncludesRulesJoin(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('LEFT JOIN sg.rules r', $dql);
        $this->assertStringContainsString('sg, cv, r', $dql);
    }

    public function testListSecurityGroupsForUserJoinsCrawlAndUser(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 42;

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('JOIN sg.crawl cv', $dql);
        $this->assertStringContainsString('JOIN cv.customer c', $dql);
        $this->assertStringContainsString('JOIN c.users u', $dql);
        $this->assertStringContainsString('u.id = :user', $dql);
        $this->assertEquals(42, $qb->getParameter('user')->getValue());
    }

    public function testListSecurityGroupsForUserFiltersGroupId(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;
        $search->groupId = 'sg-123';

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('sg.groupId LIKE :groupId', $dql);
        $this->assertEquals('%sg-123%', $qb->getParameter('groupId')->getValue());
    }

    public function testListSecurityGroupsForUserFiltersGroupName(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;
        $search->groupName = 'my-sg';

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('sg.groupName LIKE :groupName', $dql);
        $this->assertEquals('%my-sg%', $qb->getParameter('groupName')->getValue());
    }

    public function testListSecurityGroupsForUserFiltersVpcId(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;
        $search->vpcId = 'vpc-abc';

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('sg.vpcId LIKE :vpcId', $dql);
        $this->assertEquals('%vpc-abc%', $qb->getParameter('vpcId')->getValue());
    }

    public function testListSecurityGroupsForUserWithoutOptionalFilters(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringNotContainsString(':groupId', $dql);
        $this->assertStringNotContainsString(':groupName', $dql);
        $this->assertStringNotContainsString(':vpcId', $dql);
    }

    public function testListSecurityGroupsForUserOrdersByGroupId(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('ORDER BY sg.groupId ASC', $dql);
    }

    public function testListSecurityGroupsForUserWithAllFilters(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;
        $search->groupId = 'sg-all';
        $search->groupName = 'all-sg';
        $search->vpcId = 'vpc-all';

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('sg.groupId LIKE :groupId', $dql);
        $this->assertStringContainsString('sg.groupName LIKE :groupName', $dql);
        $this->assertStringContainsString('sg.vpcId LIKE :vpcId', $dql);
    }

    public function testListSecurityGroupsForUserReturnsQueryBuilder(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;

        $qb = $this->repository->listSecurityGroupsForUser($search);

        $this->assertInstanceOf(\Doctrine\ORM\QueryBuilder::class, $qb);
    }

    public function testListSecurityGroupsForUserUsesLeftJoinForRules(): void
    {
        $search = new SecurityGroupListSearch();
        $search->userId = 1;

        $qb = $this->repository->listSecurityGroupsForUser($search);
        $dql = $qb->getDQL();

        // Verify LEFT JOIN (not INNER JOIN) so SGs with zero rules are included
        $this->assertMatchesRegularExpression('/LEFT JOIN sg\.rules/', $dql);
    }
}
