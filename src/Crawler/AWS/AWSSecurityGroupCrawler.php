<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\EC2\SecurityGroup;
use App\Entity\AWS\EC2\SecurityGroupRule;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;

class AWSSecurityGroupCrawler extends AWSBaseCrawler
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
                'MaxResults' => 1000,
            ];

            if ($nextToken) {
                $params['NextToken'] = $nextToken;
            }

            $result = $ec2Client->describeSecurityGroups($params);

            foreach ($result->get('SecurityGroups') as $sgData) {
                /** @var SecurityGroup $securityGroup */
                $securityGroup = $this->denormalizer->denormalize($sgData, SecurityGroup::class, null, [
                    'object_context' => SecurityGroup::class,
                ]);

                $securityGroup->setCrawl($crawlVersion);

                $this->denormalizeRules($sgData, $securityGroup);

                $this->entityManager->persist($securityGroup);
            }

            $nextToken = $result->get('NextToken');

        } while ($nextToken);

        $this->entityManager->flush();
    }

    private function denormalizeRules(array $sgData, SecurityGroup $securityGroup): void
    {
        if (isset($sgData['IpPermissions'])) {
            foreach ($sgData['IpPermissions'] as $permission) {
                $this->createRulesFromPermission($permission, 'ingress', $securityGroup);
            }
        }

        if (isset($sgData['IpPermissionsEgress'])) {
            foreach ($sgData['IpPermissionsEgress'] as $permission) {
                $this->createRulesFromPermission($permission, 'egress', $securityGroup);
            }
        }
    }

    private function createRulesFromPermission(array $permission, string $direction, SecurityGroup $securityGroup): void
    {
        $ipRanges = $permission['IpRanges'] ?? [];
        $ipv6Ranges = $permission['Ipv6Ranges'] ?? [];

        if (empty($ipRanges) && empty($ipv6Ranges)) {
            $rule = new SecurityGroupRule();
            $rule->setIpProtocol($permission['IpProtocol'] ?? null);
            $rule->setFromPort($permission['FromPort'] ?? null);
            $rule->setToPort($permission['ToPort'] ?? null);
            $rule->setDirection($direction);
            $securityGroup->addRule($rule);
            return;
        }

        foreach ($ipRanges as $ipRange) {
            $rule = new SecurityGroupRule();
            $rule->setIpProtocol($permission['IpProtocol'] ?? null);
            $rule->setFromPort($permission['FromPort'] ?? null);
            $rule->setToPort($permission['ToPort'] ?? null);
            $rule->setCidrIp($ipRange['CidrIp'] ?? null);
            $rule->setDirection($direction);
            $securityGroup->addRule($rule);
        }

        foreach ($ipv6Ranges as $ipv6Range) {
            $rule = new SecurityGroupRule();
            $rule->setIpProtocol($permission['IpProtocol'] ?? null);
            $rule->setFromPort($permission['FromPort'] ?? null);
            $rule->setToPort($permission['ToPort'] ?? null);
            $rule->setCidrIpv6($ipv6Range['CidrIpv6'] ?? null);
            $rule->setDirection($direction);
            $securityGroup->addRule($rule);
        }
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
