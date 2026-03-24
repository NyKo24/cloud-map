<?php

namespace App\Crawler\AWS;

use App\Entity\AWS\Lambda\LambdaFunction;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Lambda\LambdaClient;

class AWSLambdaCrawler extends AWSBaseCrawler
{
    public function isGlobal(): bool
    {
        return false;
    }

    public function crawl(Credentials $credentials, string $regionName, string $accountId, CrawlVersion $crawlVersion): void
    {
        $lambdaClient = new LambdaClient([
            'credentials' => $credentials,
            'region' => $regionName,
            'version' => 'latest'
        ]);

        $nextMarker = null;

        do {
            $params = [
                'MaxItems' => 1000
            ];

            if ($nextMarker) {
                $params['Marker'] = $nextMarker;
            }

            $functions = $lambdaClient->listFunctions($params);

            foreach ($functions->get('Functions') as $functionData) {
                /** @var LambdaFunction $lambdaFunction */
                $lambdaFunction = $this->denormalizer->denormalize($functionData, LambdaFunction::class, null, [
                    'object_context' => LambdaFunction::class,
                ]);

                $lambdaFunction->setCrawl($crawlVersion);

                // Crawl function tags
                $this->crawlFunctionTags($lambdaClient, $lambdaFunction);

                $this->entityManager->persist($lambdaFunction);
            }

            $nextMarker = $functions->get('NextMarker');

        } while ($nextMarker);

        $this->entityManager->flush();
    }

    private function crawlFunctionTags(LambdaClient $lambdaClient, LambdaFunction $lambdaFunction): void
    {
        if (!$lambdaFunction->getFunctionArn()) {
            return;
        }

        try {
            $tags = $lambdaClient->listTags([
                'Resource' => $lambdaFunction->getFunctionArn()
            ]);

            $tagsData = $tags->get('Tags');
            if (!empty($tagsData)) {
                foreach ($tagsData as $key => $value) {
                    $tagData = [
                        'Key' => $key,
                        'Value' => $value
                    ];

                    $tag = $this->denormalizer->denormalize($tagData, \App\Entity\AWS\Tag::class);
                    $tag->setLambdaFunction($lambdaFunction);
                    $lambdaFunction->addTag($tag);
                }
            }
        } catch (\Exception $e) {
            // Tags access might be denied for some functions, continue crawling
        }
    }
}