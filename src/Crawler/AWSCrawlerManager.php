<?php

namespace App\Crawler;

use App\Crawler\AWS\AWSCrawlerInterface;
use App\Entity\AWS\AwsAccount;
use App\Entity\CrawlVersion;
use App\Entity\Customer;
use Aws\Credentials\Credentials;
use Aws\Sts\StsClient;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class AWSCrawlerManager
{
    const AWS_REGIONS = [
      'eu-west-3'
    ];
    /**
     * @param AWSCrawlerInterface[] $crawlers
     */

    public function __construct(
        #[AutowireIterator('crawler.aws')]
        private iterable $crawlers,
        private ManagerRegistry $registry,
        private LoggerInterface $logger
    )
    {
    }

    public function crawl(Customer $customer): void
    {
        $stsClient = new StsClient([
            'region' => 'us-east-1',
            'version' => 'latest'
        ]);

        $crawlerVersion = new CrawlVersion();

        $crawlerVersion->setVersion((string) time());
        $crawlerVersion->setCustomer($customer);

        $this->registry->getManager()->persist($crawlerVersion);
        $this->registry->getManager()->flush();

        /** @var AwsAccount $awsAccount */
        foreach ($customer->getAwsAccounts() as $awsAccount) {
            $roleArn = sprintf('arn:aws:iam::%s:role/Ext-ResourceMapCloud-Crawler', $awsAccount->getAwsId());
            try {
                $accountCredentialsResult = $stsClient->assumeRole([
                    'RoleArn' => $roleArn,
                    'RoleSessionName' => 'session'
                ]);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('[customer : "%s" (%s)]Error assuming role for account %s: %s', $customer->getName(), $customer->getId(), $awsAccount->getAwsId(), $e->getMessage()));
                continue;
            }

            $this->logger->info(sprintf('[customer : "%s" (%s)]Assumed role for account %s', $customer->getName(), $customer->getId(), $awsAccount->getAwsId()));

            $accountCredentials = new Credentials(
                $accountCredentialsResult['Credentials']['AccessKeyId'],
                $accountCredentialsResult['Credentials']['SecretAccessKey'],
                $accountCredentialsResult['Credentials']['SessionToken']
            );

            foreach (self::AWS_REGIONS as $regionName) {
                foreach ($this->crawlers as $crawler) {
                    $crawler->crawl($accountCredentials, $regionName, $awsAccount->getAwsId(), $crawlerVersion);
                }
            }
        }




    }
}
