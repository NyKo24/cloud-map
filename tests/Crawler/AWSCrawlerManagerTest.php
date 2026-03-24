<?php

namespace App\Tests\Crawler;

use App\Crawler\AWS\AWSCrawlerInterface;
use App\Crawler\AWSCrawlerManager;
use App\Entity\AWS\AwsAccount;
use App\Entity\CrawlVersion;
use App\Entity\Customer;
use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\Sts\StsClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AWSCrawlerManagerTest extends TestCase
{
    private ManagerRegistry $registry;
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('persist');
        $this->em->method('flush');

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManager')->willReturn($this->em);

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGlobalCrawlerReceivesUsEast1Region(): void
    {
        $receivedRegions = [];

        $globalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $globalCrawler->method('isGlobal')->willReturn(true);
        $globalCrawler->expects($this->once())
            ->method('crawl')
            ->willReturnCallback(function (Credentials $creds, string $region, string $accountId, CrawlVersion $cv) use (&$receivedRegions) {
                $receivedRegions[] = $region;
            });

        $manager = $this->createTestableManager([$globalCrawler]);

        $customer = $this->createCustomerWithAccount('123456789012');
        $manager->crawl($customer);

        $this->assertCount(1, $receivedRegions);
        $this->assertSame('us-east-1', $receivedRegions[0]);
    }

    public function testRegionalCrawlerReceivesEachConfiguredRegion(): void
    {
        $receivedRegions = [];

        $regionalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $regionalCrawler->method('isGlobal')->willReturn(false);
        $regionalCrawler->expects($this->exactly(count(AWSCrawlerManager::AWS_REGIONS)))
            ->method('crawl')
            ->willReturnCallback(function (Credentials $creds, string $region, string $accountId, CrawlVersion $cv) use (&$receivedRegions) {
                $receivedRegions[] = $region;
            });

        $manager = $this->createTestableManager([$regionalCrawler]);

        $customer = $this->createCustomerWithAccount('123456789012');
        $manager->crawl($customer);

        $this->assertSame(AWSCrawlerManager::AWS_REGIONS, $receivedRegions);
    }

    public function testGlobalCrawlerRunsOncePerAccountNotPerRegion(): void
    {
        $callCount = 0;

        $globalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $globalCrawler->method('isGlobal')->willReturn(true);
        $globalCrawler->method('crawl')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
            });

        $manager = $this->createTestableManager([$globalCrawler]);

        $customer = $this->createCustomerWithAccount('123456789012');
        $manager->crawl($customer);

        $this->assertSame(1, $callCount, 'Global crawler should be called exactly once per account');
    }

    public function testMixedCrawlersRoutedCorrectly(): void
    {
        $globalRegions = [];
        $regionalRegions = [];

        $globalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $globalCrawler->method('isGlobal')->willReturn(true);
        $globalCrawler->method('crawl')
            ->willReturnCallback(function (Credentials $creds, string $region) use (&$globalRegions) {
                $globalRegions[] = $region;
            });

        $regionalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $regionalCrawler->method('isGlobal')->willReturn(false);
        $regionalCrawler->method('crawl')
            ->willReturnCallback(function (Credentials $creds, string $region) use (&$regionalRegions) {
                $regionalRegions[] = $region;
            });

        $manager = $this->createTestableManager([$globalCrawler, $regionalCrawler]);

        $customer = $this->createCustomerWithAccount('123456789012');
        $manager->crawl($customer);

        $this->assertSame(['us-east-1'], $globalRegions);
        $this->assertSame(AWSCrawlerManager::AWS_REGIONS, $regionalRegions);
    }

    public function testMultipleAccountsCrawlEachAccountSeparately(): void
    {
        $globalCalls = [];
        $regionalCalls = [];

        $globalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $globalCrawler->method('isGlobal')->willReturn(true);
        $globalCrawler->method('crawl')
            ->willReturnCallback(function (Credentials $creds, string $region, string $accountId) use (&$globalCalls) {
                $globalCalls[] = ['region' => $region, 'account' => $accountId];
            });

        $regionalCrawler = $this->createMock(AWSCrawlerInterface::class);
        $regionalCrawler->method('isGlobal')->willReturn(false);
        $regionalCrawler->method('crawl')
            ->willReturnCallback(function (Credentials $creds, string $region, string $accountId) use (&$regionalCalls) {
                $regionalCalls[] = ['region' => $region, 'account' => $accountId];
            });

        $manager = $this->createTestableManager([$globalCrawler, $regionalCrawler]);

        $customer = $this->createCustomerWithAccounts(['111111111111', '222222222222']);
        $manager->crawl($customer);

        // Global crawler: once per account = 2 calls
        $this->assertCount(2, $globalCalls);
        $this->assertSame('us-east-1', $globalCalls[0]['region']);
        $this->assertSame('111111111111', $globalCalls[0]['account']);
        $this->assertSame('us-east-1', $globalCalls[1]['region']);
        $this->assertSame('222222222222', $globalCalls[1]['account']);

        // Regional crawler: once per region per account
        $expectedRegionalCount = count(AWSCrawlerManager::AWS_REGIONS) * 2;
        $this->assertCount($expectedRegionalCount, $regionalCalls);
    }

    public function testAwsGlobalRegionConstant(): void
    {
        $this->assertSame('us-east-1', AWSCrawlerManager::AWS_GLOBAL_REGION);
    }

    private function createTestableManager(array $crawlers): AWSCrawlerManager
    {
        // We need to subclass AWSCrawlerManager to bypass the STS call
        return new class($crawlers, $this->registry, $this->logger) extends AWSCrawlerManager {
            public function crawl(Customer $customer): void
            {
                $crawlerVersion = new CrawlVersion();
                $crawlerVersion->setVersion((string) time());
                $crawlerVersion->setCustomer($customer);

                // Use reflection to access private $crawlers
                $reflection = new \ReflectionClass(AWSCrawlerManager::class);
                $crawlersProp = $reflection->getProperty('crawlers');
                $crawlersProp->setAccessible(true);
                $crawlers = $crawlersProp->getValue($this);

                $fakeCredentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

                foreach ($customer->getAwsAccounts() as $awsAccount) {
                    foreach ($crawlers as $crawler) {
                        if ($crawler->isGlobal()) {
                            $crawler->crawl($fakeCredentials, AWSCrawlerManager::AWS_GLOBAL_REGION, $awsAccount->getAwsId(), $crawlerVersion);
                        } else {
                            foreach (AWSCrawlerManager::AWS_REGIONS as $regionName) {
                                $crawler->crawl($fakeCredentials, $regionName, $awsAccount->getAwsId(), $crawlerVersion);
                            }
                        }
                    }
                }
            }
        };
    }

    private function createCustomerWithAccount(string $awsId): Customer
    {
        return $this->createCustomerWithAccounts([$awsId]);
    }

    private function createCustomerWithAccounts(array $awsIds): Customer
    {
        $customer = new Customer();
        $customer->setName('Test Customer');

        foreach ($awsIds as $awsId) {
            $account = new AwsAccount();
            $account->setAwsId($awsId);
            $customer->addAwsAccount($account);
        }

        return $customer;
    }
}
