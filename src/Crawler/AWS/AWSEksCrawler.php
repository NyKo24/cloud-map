<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\EKS\EksCluster;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\EKS\EKSClient;

class AWSEksCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return false;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $eksClient = new EKSClient([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);

        $nextToken = null;

        do {
            $params = [];

            if ($nextToken) {
                $params['nextToken'] = $nextToken;
            }

            $result = $eksClient->listClusters($params);

            $clusterNames = $result->get('clusters') ?? [];

            foreach ($clusterNames as $clusterName) {
                $described = $eksClient->describeCluster([
                    'name' => $clusterName,
                ]);

                $clusterData = $described->get('cluster');

                if ($clusterData) {
                    /** @var EksCluster $cluster */
                    $cluster = $this->denormalizer->denormalize($clusterData, EksCluster::class, null, [
                        'object_context' => EksCluster::class,
                    ]);

                    $cluster->setCrawl($crawlVersion);

                    $this->entityManager->persist($cluster);
                }
            }

            $nextToken = $result->get('nextToken');
        } while ($nextToken);

        $this->entityManager->flush();
    }
}
