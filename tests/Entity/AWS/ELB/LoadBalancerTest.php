<?php

namespace App\Tests\Entity\AWS\ELB;

use App\Entity\AWS\ELB\LoadBalancer;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class LoadBalancerTest extends TestCase
{
    public function testGetStateCodeReturnsCodeWhenPresent(): void
    {
        $lb = new LoadBalancer();
        $lb->setState(['Code' => 'active', 'Reason' => '']);

        $this->assertEquals('active', $lb->getStateCode());
    }

    public function testGetStateCodeReturnsNullWhenStateIsNull(): void
    {
        $lb = new LoadBalancer();

        $this->assertNull($lb->getStateCode());
    }

    public function testGetStateCodeReturnsNullWhenCodeMissing(): void
    {
        $lb = new LoadBalancer();
        $lb->setState(['Reason' => 'some reason']);

        $this->assertNull($lb->getStateCode());
    }

    public function testGetAvailabilityZoneNamesReturnsZoneNames(): void
    {
        $lb = new LoadBalancer();
        $lb->setAvailabilityZones([
            ['ZoneName' => 'eu-west-1a', 'SubnetId' => 'subnet-123'],
            ['ZoneName' => 'eu-west-1b', 'SubnetId' => 'subnet-456'],
        ]);

        $this->assertEquals(['eu-west-1a', 'eu-west-1b'], $lb->getAvailabilityZoneNames());
    }

    public function testGetAvailabilityZoneNamesReturnsEmptyWhenNull(): void
    {
        $lb = new LoadBalancer();

        $this->assertEquals([], $lb->getAvailabilityZoneNames());
    }

    public function testSetCrawlSetsRelationship(): void
    {
        $lb = new LoadBalancer();
        $crawl = new CrawlVersion();

        $lb->setCrawl($crawl);

        $this->assertSame($crawl, $lb->getCrawl());
    }

    public function testFluentSetters(): void
    {
        $lb = new LoadBalancer();

        $result = $lb->setLoadBalancerName('my-alb')
            ->setType('application')
            ->setScheme('internet-facing')
            ->setDnsName('my-alb-123.eu-west-1.elb.amazonaws.com')
            ->setVpcId('vpc-123');

        $this->assertSame($lb, $result);
        $this->assertEquals('my-alb', $lb->getLoadBalancerName());
        $this->assertEquals('application', $lb->getType());
        $this->assertEquals('internet-facing', $lb->getScheme());
        $this->assertEquals('my-alb-123.eu-west-1.elb.amazonaws.com', $lb->getDnsName());
        $this->assertEquals('vpc-123', $lb->getVpcId());
    }
}
