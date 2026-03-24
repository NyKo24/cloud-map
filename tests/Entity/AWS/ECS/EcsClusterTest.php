<?php

namespace App\Tests\Entity\AWS\ECS;

use App\Entity\AWS\ECS\EcsCluster;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class EcsClusterTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $cluster = new EcsCluster();

        $cluster->setClusterName('my-cluster');
        $this->assertSame('my-cluster', $cluster->getClusterName());

        $cluster->setClusterArn('arn:aws:ecs:us-east-1:123456789012:cluster/my-cluster');
        $this->assertSame('arn:aws:ecs:us-east-1:123456789012:cluster/my-cluster', $cluster->getClusterArn());

        $cluster->setStatus('ACTIVE');
        $this->assertSame('ACTIVE', $cluster->getStatus());

        $cluster->setRegisteredContainerInstancesCount(3);
        $this->assertSame(3, $cluster->getRegisteredContainerInstancesCount());

        $cluster->setRunningTasksCount(5);
        $this->assertSame(5, $cluster->getRunningTasksCount());

        $cluster->setActiveServicesCount(2);
        $this->assertSame(2, $cluster->getActiveServicesCount());
    }

    public function testCrawlVersionRelationship(): void
    {
        $cluster = new EcsCluster();
        $crawlVersion = new CrawlVersion();

        $cluster->setCrawl($crawlVersion);
        $this->assertSame($crawlVersion, $cluster->getCrawl());
    }

    public function testCrawlVersionAddEcsCluster(): void
    {
        $crawlVersion = new CrawlVersion();
        $cluster = new EcsCluster();

        $crawlVersion->addEcsCluster($cluster);

        $this->assertCount(1, $crawlVersion->getEcsClusters());
        $this->assertSame($crawlVersion, $cluster->getCrawl());
    }

    public function testCrawlVersionAddEcsClusterDoesNotDuplicate(): void
    {
        $crawlVersion = new CrawlVersion();
        $cluster = new EcsCluster();

        $crawlVersion->addEcsCluster($cluster);
        $crawlVersion->addEcsCluster($cluster);

        $this->assertCount(1, $crawlVersion->getEcsClusters());
    }

    public function testCrawlVersionRemoveEcsCluster(): void
    {
        $crawlVersion = new CrawlVersion();
        $cluster = new EcsCluster();

        $crawlVersion->addEcsCluster($cluster);
        $this->assertCount(1, $crawlVersion->getEcsClusters());

        $crawlVersion->removeEcsCluster($cluster);
        $this->assertCount(0, $crawlVersion->getEcsClusters());
        $this->assertNull($cluster->getCrawl());
    }

    public function testDefaultValues(): void
    {
        $cluster = new EcsCluster();

        $this->assertNull($cluster->getId());
        $this->assertNull($cluster->getClusterName());
        $this->assertNull($cluster->getClusterArn());
        $this->assertNull($cluster->getStatus());
        $this->assertNull($cluster->getRegisteredContainerInstancesCount());
        $this->assertNull($cluster->getRunningTasksCount());
        $this->assertNull($cluster->getActiveServicesCount());
        $this->assertNull($cluster->getCrawl());
    }

    public function testSettersReturnSelf(): void
    {
        $cluster = new EcsCluster();

        $this->assertSame($cluster, $cluster->setClusterName('test'));
        $this->assertSame($cluster, $cluster->setClusterArn('arn'));
        $this->assertSame($cluster, $cluster->setStatus('ACTIVE'));
        $this->assertSame($cluster, $cluster->setRegisteredContainerInstancesCount(0));
        $this->assertSame($cluster, $cluster->setRunningTasksCount(0));
        $this->assertSame($cluster, $cluster->setActiveServicesCount(0));
        $this->assertSame($cluster, $cluster->setCrawl(new CrawlVersion()));
    }
}
