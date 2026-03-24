<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\Route53\HostedZone;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Route53\Route53Client;

class AWSRoute53Crawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return true;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $route53Client = $this->createRoute53Client($credentials, $regionName);

        $marker = null;

        do {
            $params = [
                'MaxItems' => '100',
            ];

            if ($marker) {
                $params['Marker'] = $marker;
            }

            $result = $route53Client->listHostedZones($params);

            foreach ($result->get('HostedZones') ?? [] as $zoneData) {
                /** @var HostedZone $hostedZone */
                $hostedZone = $this->denormalizer->denormalize($zoneData, HostedZone::class, null, [
                    'object_context' => HostedZone::class,
                ]);

                if (isset($zoneData['Config'])) {
                    $hostedZone->setComment($zoneData['Config']['Comment'] ?? null);
                    $hostedZone->setPrivateZone($zoneData['Config']['PrivateZone'] ?? false);
                }

                $hostedZone->setCrawl($crawlVersion);

                $this->entityManager->persist($hostedZone);
            }

            $marker = $result->get('IsTruncated') ? $result->get('NextMarker') : null;
        } while ($marker);

        $this->entityManager->flush();
    }

    protected function createRoute53Client(Credentials $credentials, string $regionName): Route53Client
    {
        return new Route53Client([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);
    }
}
