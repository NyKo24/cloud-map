<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSBaseCrawler;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSBaseCrawlerTest extends TestCase
{
    public function testIsGlobalReturnsFalseByDefault(): void
    {
        $crawler = new class(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        ) extends AWSBaseCrawler {
            public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
            {
            }
        };

        $this->assertFalse($crawler->isGlobal());
    }

    public function testIsGlobalCanBeOverriddenToTrue(): void
    {
        $crawler = new class(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        ) extends AWSBaseCrawler {
            public function isGlobal(): bool
            {
                return true;
            }

            public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
            {
            }
        };

        $this->assertTrue($crawler->isGlobal());
    }
}
