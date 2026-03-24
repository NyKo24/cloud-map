<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\EC2\Ec2Instance;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;

class AWSEC2InstanceCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return false;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $ec2Client = $this->createEc2Client($credentials, $regionName);

        $nextToken = null;

        do {
            $params = [
                'MaxResults' => 1000
            ];

            if ($nextToken) {
                $params['NextToken'] = $nextToken;
            }

            $result = $ec2Client->describeInstances($params);

            foreach ($result->get('Reservations') as $reservation) {
                foreach ($reservation['Instances'] as $instanceData) {
                    /** @var Ec2Instance $ec2Instance */
                    $ec2Instance = $this->denormalizer->denormalize($instanceData, Ec2Instance::class, null, [
                        'object_context' => Ec2Instance::class,
                    ]);

                    $ec2Instance->setCrawl($crawlVersion);

                    $this->entityManager->persist($ec2Instance);
                }
            }

            $nextToken = $result->get('NextToken');

        } while ($nextToken);

        $this->entityManager->flush();
    }

    protected function createEc2Client(Credentials $credentials, string $regionName): Ec2Client
    {
        return new Ec2Client([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);
    }
}
