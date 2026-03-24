<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\VPC\Vpc;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class AWSVpcCrawler extends AWSBaseCrawler
{
    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $vpcClient = $this->createEc2Client($credentials, $regionName);

        $vpcs = $vpcClient->describeVpcs([
            'MaxResults' => 1000
        ]);

        foreach ($vpcs->get('Vpcs') as $vpcData) {
            /** @var Vpc $vpc */
            $vpc = $this->denormalizer->denormalize($vpcData, VPC::class, null, [
                'object_context' => Vpc::class,
                //AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true
                //AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                //AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                //AbstractNormalizer::OBJECT_TO_POPULATE => new Vpc()
            ]);
            $vpc->setCrawl($crawlVersion);


            $this->entityManager->persist($vpc);
        }

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
