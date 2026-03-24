<?php

namespace App\Tests\Entity\AWS\EC2;

use App\Entity\AWS\EC2\SecurityGroup;
use App\Entity\AWS\EC2\SecurityGroupRule;
use PHPUnit\Framework\TestCase;

class SecurityGroupTest extends TestCase
{
    public function testGetNameTagReturnsNameWhenPresent(): void
    {
        $sg = new SecurityGroup();
        $sg->setTags([
            ['Key' => 'Environment', 'Value' => 'prod'],
            ['Key' => 'Name', 'Value' => 'my-security-group'],
        ]);

        $this->assertEquals('my-security-group', $sg->getNameTag());
    }

    public function testGetNameTagReturnsNullWhenNoNameTag(): void
    {
        $sg = new SecurityGroup();
        $sg->setTags([
            ['Key' => 'Environment', 'Value' => 'prod'],
        ]);

        $this->assertNull($sg->getNameTag());
    }

    public function testGetNameTagReturnsNullWhenTagsAreNull(): void
    {
        $sg = new SecurityGroup();

        $this->assertNull($sg->getNameTag());
    }

    public function testGetNameTagReturnsNullWhenValueMissing(): void
    {
        $sg = new SecurityGroup();
        $sg->setTags([
            ['Key' => 'Name'],
        ]);

        $this->assertNull($sg->getNameTag());
    }

    public function testGetIngressAndEgressRulesFiltering(): void
    {
        $sg = new SecurityGroup();

        $ingressRule = new SecurityGroupRule();
        $ingressRule->setDirection('ingress');
        $sg->addRule($ingressRule);

        $egressRule = new SecurityGroupRule();
        $egressRule->setDirection('egress');
        $sg->addRule($egressRule);

        $ingress2 = new SecurityGroupRule();
        $ingress2->setDirection('ingress');
        $sg->addRule($ingress2);

        $this->assertCount(3, $sg->getRules());
        $this->assertCount(2, $sg->getIngressRules());
        $this->assertCount(1, $sg->getEgressRules());
    }

    public function testAddRuleSetsBackReference(): void
    {
        $sg = new SecurityGroup();
        $rule = new SecurityGroupRule();

        $sg->addRule($rule);

        $this->assertSame($sg, $rule->getSecurityGroup());
        $this->assertCount(1, $sg->getRules());
    }

    public function testAddRuleDoesNotDuplicate(): void
    {
        $sg = new SecurityGroup();
        $rule = new SecurityGroupRule();

        $sg->addRule($rule);
        $sg->addRule($rule);

        $this->assertCount(1, $sg->getRules());
    }

    public function testRemoveRule(): void
    {
        $sg = new SecurityGroup();
        $rule = new SecurityGroupRule();

        $sg->addRule($rule);
        $this->assertCount(1, $sg->getRules());

        $sg->removeRule($rule);
        $this->assertCount(0, $sg->getRules());
        $this->assertNull($rule->getSecurityGroup());
    }
}
