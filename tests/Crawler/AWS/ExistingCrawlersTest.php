<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSLambdaCrawler;
use App\Crawler\AWS\AWSVpcCrawler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ExistingCrawlersTest extends TestCase
{
    public function testVpcCrawlerIsNotGlobal(): void
    {
        $crawler = new AWSVpcCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testLambdaCrawlerIsNotGlobal(): void
    {
        $crawler = new AWSLambdaCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        );

        $this->assertFalse($crawler->isGlobal());
    }
}
