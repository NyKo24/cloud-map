<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\RDS\RdsInstance;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Rds\RdsClient;

class AWSRdsCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return false;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $rdsClient = $this->createRdsClient($credentials, $regionName);

        $marker = null;

        do {
            $params = [
                'MaxRecords' => 100
            ];

            if ($marker) {
                $params['Marker'] = $marker;
            }

            $result = $rdsClient->describeDBInstances($params);

            foreach ($result->get('DBInstances') as $instanceData) {
                /** @var RdsInstance $rdsInstance */
                $rdsInstance = $this->denormalizer->denormalize($instanceData, RdsInstance::class, null, [
                    'object_context' => RdsInstance::class,
                ]);

                $rdsInstance->setCrawl($crawlVersion);

                $this->entityManager->persist($rdsInstance);
            }

            $marker = $result->get('Marker');

        } while ($marker);

        $this->entityManager->flush();
    }

    protected function createRdsClient(Credentials $credentials, string $regionName): RdsClient
    {
        return new RdsClient([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest'
        ]);
    }
}
