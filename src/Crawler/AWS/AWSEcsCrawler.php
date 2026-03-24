<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\ECS\EcsCluster;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ecs\EcsClient;

class AWSEcsCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return false;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $ecsClient = $this->createEcsClient($credentials, $regionName);

        $nextToken = null;

        do {
            $params = [];

            if ($nextToken) {
                $params['nextToken'] = $nextToken;
            }

            $result = $ecsClient->listClusters($params);

            $clusterArns = $result->get('clusterArns') ?? [];

            if (!empty($clusterArns)) {
                $described = $ecsClient->describeClusters([
                    'clusters' => $clusterArns,
                ]);

                foreach ($described->get('clusters') ?? [] as $clusterData) {
                    /** @var EcsCluster $cluster */
                    $cluster = $this->denormalizer->denormalize($clusterData, EcsCluster::class, null, [
                        'object_context' => EcsCluster::class,
                    ]);

                    $cluster->setCrawl($crawlVersion);

                    $this->entityManager->persist($cluster);
                }
            }

            $nextToken = $result->get('nextToken');
        } while ($nextToken);

        $this->entityManager->flush();
    }

    protected function createEcsClient(Credentials $credentials, string $regionName): EcsClient
    {
        return new EcsClient([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);
    }
}
