<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\IAM\IamRole;
use App\Entity\AWS\IAM\IamUser;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Iam\IamClient;

class AWSIamCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return true;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $iamClient = $this->createIamClient($credentials, $regionName);

        $this->crawlRoles($iamClient, $crawlVersion);
        $this->crawlUsers($iamClient, $crawlVersion);

        $this->entityManager->flush();
    }

    private function crawlRoles(IamClient $iamClient, CrawlVersion $crawlVersion): void
    {
        $marker = null;

        do {
            $params = [
                'MaxItems' => 1000,
            ];

            if ($marker) {
                $params['Marker'] = $marker;
            }

            $result = $iamClient->listRoles($params);

            foreach ($result->get('Roles') ?? [] as $roleData) {
                /** @var IamRole $iamRole */
                $iamRole = $this->denormalizer->denormalize($roleData, IamRole::class, null, [
                    'object_context' => IamRole::class,
                ]);

                $iamRole->setCrawl($crawlVersion);

                $this->entityManager->persist($iamRole);
            }

            $marker = $result->get('IsTruncated') ? $result->get('Marker') : null;
        } while ($marker);
    }

    private function crawlUsers(IamClient $iamClient, CrawlVersion $crawlVersion): void
    {
        $marker = null;

        do {
            $params = [
                'MaxItems' => 1000,
            ];

            if ($marker) {
                $params['Marker'] = $marker;
            }

            $result = $iamClient->listUsers($params);

            foreach ($result->get('Users') ?? [] as $userData) {
                /** @var IamUser $iamUser */
                $iamUser = $this->denormalizer->denormalize($userData, IamUser::class, null, [
                    'object_context' => IamUser::class,
                ]);

                $iamUser->setCrawl($crawlVersion);

                $this->entityManager->persist($iamUser);
            }

            $marker = $result->get('IsTruncated') ? $result->get('Marker') : null;
        } while ($marker);
    }

    protected function createIamClient(Credentials $credentials, string $regionName): IamClient
    {
        return new IamClient([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest',
        ]);
    }
}
