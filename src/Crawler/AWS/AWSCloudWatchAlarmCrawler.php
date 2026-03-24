<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\CloudWatch\CloudWatchAlarm;
use App\Entity\CrawlVersion;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Credentials\Credentials;

class AWSCloudWatchAlarmCrawler extends AWSBaseCrawler
{
    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $cloudWatchClient = $this->createCloudWatchClient($credentials, $regionName);

        $nextToken = null;

        do {
            $params = [];

            if ($nextToken) {
                $params['NextToken'] = $nextToken;
            }

            $result = $cloudWatchClient->describeAlarms($params);

            foreach ($result->get('MetricAlarms') ?? [] as $alarmData) {
                /** @var CloudWatchAlarm $alarm */
                $alarm = $this->denormalizer->denormalize($alarmData, CloudWatchAlarm::class, null, [
                    'object_context' => CloudWatchAlarm::class,
                ]);

                $alarm->setCrawl($crawlVersion);

                $this->entityManager->persist($alarm);
            }

            $nextToken = $result->get('NextToken');
        } while ($nextToken);

        $this->entityManager->flush();
    }

    protected function createCloudWatchClient(Credentials $credentials, string $regionName): CloudWatchClient
    {
        return new CloudWatchClient([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);
    }
}
