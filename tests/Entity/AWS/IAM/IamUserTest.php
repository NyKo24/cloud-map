<?php

namespace App\Tests\Entity\AWS\IAM;

use App\Entity\AWS\IAM\IamUser;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class IamUserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $user = new IamUser();

        $user->setUserName('my-user');
        $this->assertSame('my-user', $user->getUserName());

        $user->setUserId('AIDAEXAMPLE');
        $this->assertSame('AIDAEXAMPLE', $user->getUserId());

        $user->setArn('arn:aws:iam::123456789012:user/my-user');
        $this->assertSame('arn:aws:iam::123456789012:user/my-user', $user->getArn());

        $user->setPath('/');
        $this->assertSame('/', $user->getPath());

        $date = new \DateTime('2024-02-01');
        $user->setCreateDate($date);
        $this->assertSame($date, $user->getCreateDate());

        $lastUsed = new \DateTime('2024-03-15');
        $user->setPasswordLastUsed($lastUsed);
        $this->assertSame($lastUsed, $user->getPasswordLastUsed());
    }

    public function testCrawlVersionRelationship(): void
    {
        $user = new IamUser();
        $crawlVersion = new CrawlVersion();

        $user->setCrawl($crawlVersion);
        $this->assertSame($crawlVersion, $user->getCrawl());
    }

    public function testCrawlVersionAddIamUser(): void
    {
        $crawlVersion = new CrawlVersion();
        $user = new IamUser();

        $crawlVersion->addIamUser($user);

        $this->assertCount(1, $crawlVersion->getIamUsers());
        $this->assertSame($crawlVersion, $user->getCrawl());
    }

    public function testCrawlVersionAddIamUserDoesNotDuplicate(): void
    {
        $crawlVersion = new CrawlVersion();
        $user = new IamUser();

        $crawlVersion->addIamUser($user);
        $crawlVersion->addIamUser($user);

        $this->assertCount(1, $crawlVersion->getIamUsers());
    }

    public function testCrawlVersionRemoveIamUser(): void
    {
        $crawlVersion = new CrawlVersion();
        $user = new IamUser();

        $crawlVersion->addIamUser($user);
        $this->assertCount(1, $crawlVersion->getIamUsers());

        $crawlVersion->removeIamUser($user);
        $this->assertCount(0, $crawlVersion->getIamUsers());
        $this->assertNull($user->getCrawl());
    }

    public function testDefaultValues(): void
    {
        $user = new IamUser();

        $this->assertNull($user->getId());
        $this->assertNull($user->getUserName());
        $this->assertNull($user->getUserId());
        $this->assertNull($user->getArn());
        $this->assertNull($user->getPath());
        $this->assertNull($user->getCreateDate());
        $this->assertNull($user->getPasswordLastUsed());
        $this->assertNull($user->getCrawl());
    }
}
