<?php

namespace App\Tests\Entity\AWS\EC2;

use App\Entity\AWS\EC2\SecurityGroupRule;
use PHPUnit\Framework\TestCase;

class SecurityGroupRuleTest extends TestCase
{
    public function testGetPortRangeReturnsAllForProtocolMinusOne(): void
    {
        $rule = new SecurityGroupRule();
        $rule->setIpProtocol('-1');
        $rule->setFromPort(null);
        $rule->setToPort(null);

        $this->assertEquals('All', $rule->getPortRange());
    }

    public function testGetPortRangeReturnsSinglePortWhenEqual(): void
    {
        $rule = new SecurityGroupRule();
        $rule->setIpProtocol('tcp');
        $rule->setFromPort(443);
        $rule->setToPort(443);

        $this->assertEquals('443', $rule->getPortRange());
    }

    public function testGetPortRangeReturnsRangeWhenDifferent(): void
    {
        $rule = new SecurityGroupRule();
        $rule->setIpProtocol('tcp');
        $rule->setFromPort(8080);
        $rule->setToPort(8090);

        $this->assertEquals('8080-8090', $rule->getPortRange());
    }

    public function testGetSourceReturnsCidrIpWhenSet(): void
    {
        $rule = new SecurityGroupRule();
        $rule->setCidrIp('10.0.0.0/8');

        $this->assertEquals('10.0.0.0/8', $rule->getSource());
    }

    public function testGetSourceReturnsCidrIpv6WhenIpv4Null(): void
    {
        $rule = new SecurityGroupRule();
        $rule->setCidrIpv6('::/0');

        $this->assertEquals('::/0', $rule->getSource());
    }

    public function testGetSourcePrefersCidrIpOverIpv6(): void
    {
        $rule = new SecurityGroupRule();
        $rule->setCidrIp('0.0.0.0/0');
        $rule->setCidrIpv6('::/0');

        $this->assertEquals('0.0.0.0/0', $rule->getSource());
    }

    public function testGetSourceReturnsNullWhenBothNull(): void
    {
        $rule = new SecurityGroupRule();

        $this->assertNull($rule->getSource());
    }
}
