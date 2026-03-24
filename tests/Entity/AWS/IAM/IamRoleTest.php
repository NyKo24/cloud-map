<?php

namespace App\Tests\Entity\AWS\IAM;

use App\Entity\AWS\IAM\IamRole;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class IamRoleTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $role = new IamRole();

        $role->setRoleName('my-role');
        $this->assertSame('my-role', $role->getRoleName());

        $role->setRoleId('AROAEXAMPLE');
        $this->assertSame('AROAEXAMPLE', $role->getRoleId());

        $role->setArn('arn:aws:iam::123456789012:role/my-role');
        $this->assertSame('arn:aws:iam::123456789012:role/my-role', $role->getArn());

        $role->setPath('/service-role/');
        $this->assertSame('/service-role/', $role->getPath());

        $date = new \DateTime('2024-01-15');
        $role->setCreateDate($date);
        $this->assertSame($date, $role->getCreateDate());

        $role->setDescription('A test role');
        $this->assertSame('A test role', $role->getDescription());

        $role->setMaxSessionDuration(3600);
        $this->assertSame(3600, $role->getMaxSessionDuration());
    }

    public function testCrawlVersionRelationship(): void
    {
        $role = new IamRole();
        $crawlVersion = new CrawlVersion();

        $role->setCrawl($crawlVersion);
        $this->assertSame($crawlVersion, $role->getCrawl());
    }

    public function testCrawlVersionAddIamRole(): void
    {
        $crawlVersion = new CrawlVersion();
        $role = new IamRole();

        $crawlVersion->addIamRole($role);

        $this->assertCount(1, $crawlVersion->getIamRoles());
        $this->assertSame($crawlVersion, $role->getCrawl());
    }

    public function testCrawlVersionAddIamRoleDoesNotDuplicate(): void
    {
        $crawlVersion = new CrawlVersion();
        $role = new IamRole();

        $crawlVersion->addIamRole($role);
        $crawlVersion->addIamRole($role);

        $this->assertCount(1, $crawlVersion->getIamRoles());
    }

    public function testCrawlVersionRemoveIamRole(): void
    {
        $crawlVersion = new CrawlVersion();
        $role = new IamRole();

        $crawlVersion->addIamRole($role);
        $this->assertCount(1, $crawlVersion->getIamRoles());

        $crawlVersion->removeIamRole($role);
        $this->assertCount(0, $crawlVersion->getIamRoles());
        $this->assertNull($role->getCrawl());
    }

    public function testDefaultValues(): void
    {
        $role = new IamRole();

        $this->assertNull($role->getId());
        $this->assertNull($role->getRoleName());
        $this->assertNull($role->getRoleId());
        $this->assertNull($role->getArn());
        $this->assertNull($role->getPath());
        $this->assertNull($role->getCreateDate());
        $this->assertNull($role->getDescription());
        $this->assertNull($role->getMaxSessionDuration());
        $this->assertNull($role->getCrawl());
    }
}
