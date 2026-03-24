<?php

namespace App\Tests\Entity\AWS\EKS;

use App\Entity\AWS\EKS\EksCluster;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class EksClusterTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $cluster = new EksCluster();

        $cluster->setName('my-eks-cluster');
        $this->assertSame('my-eks-cluster', $cluster->getName());

        $cluster->setArn('arn:aws:eks:us-east-1:123456789012:cluster/my-eks-cluster');
        $this->assertSame('arn:aws:eks:us-east-1:123456789012:cluster/my-eks-cluster', $cluster->getArn());

        $cluster->setVersion('1.28');
        $this->assertSame('1.28', $cluster->getVersion());

        $cluster->setStatus('ACTIVE');
        $this->assertSame('ACTIVE', $cluster->getStatus());

        $cluster->setPlatformVersion('eks.5');
        $this->assertSame('eks.5', $cluster->getPlatformVersion());

        $cluster->setEndpoint('https://example.eks.amazonaws.com');
        $this->assertSame('https://example.eks.amazonaws.com', $cluster->getEndpoint());

        $cluster->setRoleArn('arn:aws:iam::123456789012:role/eks-role');
        $this->assertSame('arn:aws:iam::123456789012:role/eks-role', $cluster->getRoleArn());
    }

    public function testCrawlVersionRelationship(): void
    {
        $cluster = new EksCluster();
        $crawlVersion = new CrawlVersion();

        $cluster->setCrawl($crawlVersion);
        $this->assertSame($crawlVersion, $cluster->getCrawl());
    }

    public function testCrawlVersionAddEksCluster(): void
    {
        $crawlVersion = new CrawlVersion();
        $cluster = new EksCluster();

        $crawlVersion->addEksCluster($cluster);

        $this->assertCount(1, $crawlVersion->getEksClusters());
        $this->assertSame($crawlVersion, $cluster->getCrawl());
    }

    public function testCrawlVersionAddEksClusterDoesNotDuplicate(): void
    {
        $crawlVersion = new CrawlVersion();
        $cluster = new EksCluster();

        $crawlVersion->addEksCluster($cluster);
        $crawlVersion->addEksCluster($cluster);

        $this->assertCount(1, $crawlVersion->getEksClusters());
    }

    public function testCrawlVersionRemoveEksCluster(): void
    {
        $crawlVersion = new CrawlVersion();
        $cluster = new EksCluster();

        $crawlVersion->addEksCluster($cluster);
        $this->assertCount(1, $crawlVersion->getEksClusters());

        $crawlVersion->removeEksCluster($cluster);
        $this->assertCount(0, $crawlVersion->getEksClusters());
        $this->assertNull($cluster->getCrawl());
    }

    public function testDefaultValues(): void
    {
        $cluster = new EksCluster();

        $this->assertNull($cluster->getId());
        $this->assertNull($cluster->getName());
        $this->assertNull($cluster->getArn());
        $this->assertNull($cluster->getVersion());
        $this->assertNull($cluster->getStatus());
        $this->assertNull($cluster->getPlatformVersion());
        $this->assertNull($cluster->getEndpoint());
        $this->assertNull($cluster->getRoleArn());
        $this->assertNull($cluster->getCrawl());
    }

    public function testSettersReturnSelf(): void
    {
        $cluster = new EksCluster();

        $this->assertSame($cluster, $cluster->setName('test'));
        $this->assertSame($cluster, $cluster->setArn('arn'));
        $this->assertSame($cluster, $cluster->setVersion('1.28'));
        $this->assertSame($cluster, $cluster->setStatus('ACTIVE'));
        $this->assertSame($cluster, $cluster->setPlatformVersion('eks.5'));
        $this->assertSame($cluster, $cluster->setEndpoint('https://example.com'));
        $this->assertSame($cluster, $cluster->setRoleArn('arn:role'));
        $this->assertSame($cluster, $cluster->setCrawl(new CrawlVersion()));
    }
}
