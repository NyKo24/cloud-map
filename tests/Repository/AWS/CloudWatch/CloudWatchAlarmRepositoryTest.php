<?php

namespace App\Tests\Repository\AWS\CloudWatch;

use App\Repository\AWS\CloudWatch\CloudWatchAlarmRepository;
use App\Search\CloudWatchAlarmListSearch;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class CloudWatchAlarmRepositoryTest extends TestCase
{
    private CloudWatchAlarmRepository $repository;

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

        $this->repository = new CloudWatchAlarmRepository($registry);
    }

    public function testListCloudWatchAlarmsForUserJoinsCrawlAndUser(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 42;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('JOIN cwa.crawl cv', $dql);
        $this->assertStringContainsString('JOIN cv.customer c', $dql);
        $this->assertStringContainsString('JOIN c.users u', $dql);
        $this->assertStringContainsString('u.id = :user', $dql);
        $this->assertEquals(42, $qb->getParameter('user')->getValue());
    }

    public function testListCloudWatchAlarmsForUserFiltersAlarmName(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->alarmName = 'cpu-alarm';

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.alarmName LIKE :alarmName', $dql);
        $this->assertEquals('%cpu-alarm%', $qb->getParameter('alarmName')->getValue());
    }

    public function testListCloudWatchAlarmsForUserFiltersStateValue(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->stateValue = 'ALARM';

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.stateValue = :stateValue', $dql);
        $this->assertEquals('ALARM', $qb->getParameter('stateValue')->getValue());
    }

    public function testListCloudWatchAlarmsForUserFiltersMetricName(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->metricName = 'CPUUtilization';

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.metricName LIKE :metricName', $dql);
        $this->assertEquals('%CPUUtilization%', $qb->getParameter('metricName')->getValue());
    }

    public function testListCloudWatchAlarmsForUserFiltersNamespace(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->namespace = 'AWS/EC2';

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.namespace LIKE :namespace', $dql);
        $this->assertEquals('%AWS/EC2%', $qb->getParameter('namespace')->getValue());
    }

    public function testListCloudWatchAlarmsForUserFiltersActionsEnabled(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->actionsEnabled = true;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.actionsEnabled = :actionsEnabled', $dql);
        $this->assertEquals(true, $qb->getParameter('actionsEnabled')->getValue());
    }

    public function testListCloudWatchAlarmsForUserFiltersActionsEnabledFalse(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->actionsEnabled = false;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.actionsEnabled = :actionsEnabled', $dql);
        $this->assertEquals(false, $qb->getParameter('actionsEnabled')->getValue());
    }

    public function testListCloudWatchAlarmsForUserWithoutOptionalFilters(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringNotContainsString(':alarmName', $dql);
        $this->assertStringNotContainsString(':stateValue', $dql);
        $this->assertStringNotContainsString(':metricName', $dql);
        $this->assertStringNotContainsString(':namespace', $dql);
        $this->assertStringNotContainsString(':actionsEnabled', $dql);
    }

    public function testListCloudWatchAlarmsForUserOrdersByAlarmName(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('ORDER BY cwa.alarmName ASC', $dql);
    }

    public function testListCloudWatchAlarmsForUserWithAllFilters(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;
        $search->alarmName = 'test-alarm';
        $search->stateValue = 'OK';
        $search->metricName = 'CPUUtilization';
        $search->namespace = 'AWS/EC2';
        $search->actionsEnabled = true;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa.alarmName LIKE :alarmName', $dql);
        $this->assertStringContainsString('cwa.stateValue = :stateValue', $dql);
        $this->assertStringContainsString('cwa.metricName LIKE :metricName', $dql);
        $this->assertStringContainsString('cwa.namespace LIKE :namespace', $dql);
        $this->assertStringContainsString('cwa.actionsEnabled = :actionsEnabled', $dql);
    }

    public function testListCloudWatchAlarmsForUserReturnsQueryBuilder(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);

        $this->assertInstanceOf(\Doctrine\ORM\QueryBuilder::class, $qb);
    }

    public function testListCloudWatchAlarmsForUserSelectsCrawlVersion(): void
    {
        $search = new CloudWatchAlarmListSearch();
        $search->userId = 1;

        $qb = $this->repository->listCloudWatchAlarmsForUser($search);
        $dql = $qb->getDQL();

        $this->assertStringContainsString('cwa, cv', $dql);
    }
}
