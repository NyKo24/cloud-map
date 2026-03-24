<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\ELB\LoadBalancer;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client;

class AWSLoadBalancerCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return false;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $elbClient = $this->createElbClient($credentials, $regionName);

        $marker = null;

        do {
            $params = [];

            if ($marker) {
                $params['Marker'] = $marker;
            }

            $result = $elbClient->describeLoadBalancers($params);

            foreach ($result->get('LoadBalancers') as $lbData) {
                /** @var LoadBalancer $loadBalancer */
                $loadBalancer = $this->denormalizer->denormalize($lbData, LoadBalancer::class, null, [
                    'object_context' => LoadBalancer::class,
                ]);

                $loadBalancer->setCrawl($crawlVersion);

                $this->entityManager->persist($loadBalancer);
            }

            $marker = $result->get('NextMarker');

        } while ($marker);

        $this->entityManager->flush();
    }

    protected function createElbClient(Credentials $credentials, string $regionName): ElasticLoadBalancingV2Client
    {
        return new ElasticLoadBalancingV2Client([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest'
        ]);
    }
}
