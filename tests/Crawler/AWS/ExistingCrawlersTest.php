<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSEC2InstanceCrawler;
use App\Crawler\AWS\AWSLambdaCrawler;
use App\Crawler\AWS\AWSRdsCrawler;
use App\Crawler\AWS\AWSS3BucketCrawler;
use App\Crawler\AWS\AWSSecurityGroupCrawler;
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

    public function testEc2InstanceCrawlerIsNotGlobal(): void
    {
        $crawler = new AWSEC2InstanceCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testRdsCrawlerIsNotGlobal(): void
    {
        $crawler = new AWSRdsCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testSecurityGroupCrawlerIsNotGlobal(): void
    {
        $crawler = new AWSSecurityGroupCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        );

        $this->assertFalse($crawler->isGlobal());
    }

    public function testS3BucketCrawlerIsGlobal(): void
    {
        $crawler = new AWSS3BucketCrawler(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(SerializerInterface::class),
            $this->createMock(DenormalizerInterface::class),
        );

        $this->assertTrue($crawler->isGlobal());
    }
}
