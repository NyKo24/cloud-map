<?php

namespace App\Tests\Crawler\AWS;

use App\Crawler\AWS\AWSLambdaCrawler;
use App\Entity\AWS\Lambda\LambdaFunction;
use App\Entity\AWS\Tag;
use App\Entity\CrawlVersion;
use Aws\Credentials\Credentials;
use Aws\Lambda\LambdaClient;
use Aws\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AWSLambdaCrawlerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DenormalizerInterface $denormalizer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
    }

    public function testCrawlDenormalizesFunctionsAndPersists(): void
    {
        $functionData = [
            'FunctionName' => 'my-function',
            'FunctionArn' => 'arn:aws:lambda:eu-west-1:123456789012:function:my-function',
            'Runtime' => 'nodejs18.x',
        ];

        $lambdaFunction = new LambdaFunction();
        $lambdaFunction->setFunctionName('my-function');
        $lambdaFunction->setFunctionArn('arn:aws:lambda:eu-west-1:123456789012:function:my-function');

        $tag = new Tag();
        $tag->setKeyName('env');
        $tag->setValue('prod');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(function (array $data, string $type) use ($lambdaFunction, $tag) {
                if ($type === LambdaFunction::class) {
                    return $lambdaFunction;
                }
                if ($type === Tag::class) {
                    return $tag;
                }
                $this->fail("Unexpected denormalize call for type: $type");
            });

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($lambdaFunction);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawlVersion = new CrawlVersion();
        $credentials = new Credentials('fake-key', 'fake-secret', 'fake-token');

        $listFunctionsResult = new Result([
            'Functions' => [$functionData],
            'NextMarker' => null,
        ]);

        $listTagsResult = new Result([
            'Tags' => ['env' => 'prod'],
        ]);

        $crawler = $this->createCrawlerWithMockClient([
            'listFunctions' => [$listFunctionsResult],
            'listTags' => $listTagsResult,
        ]);
        $crawler->crawl($credentials, 'eu-west-1', '123456789012', $crawlVersion);

        $this->assertSame($crawlVersion, $lambdaFunction->getCrawl());
        $this->assertCount(1, $lambdaFunction->getTags());
        $this->assertSame($tag, $lambdaFunction->getTags()->first());
    }

    public function testCrawlHandlesEmptyFunctions(): void
    {
        $this->denormalizer->expects($this->never())
            ->method('denormalize');

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $mockResult = new Result([
            'Functions' => [],
            'NextMarker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([
            'listFunctions' => [$mockResult],
            'listTags' => new Result(['Tags' => []]),
        ]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'us-east-1', '123', new CrawlVersion());
    }

    public function testCrawlHandlesMarkerPagination(): void
    {
        $functionData1 = [
            'FunctionName' => 'func-page1',
            'FunctionArn' => 'arn:aws:lambda:eu-west-1:123:function:func-page1',
        ];
        $functionData2 = [
            'FunctionName' => 'func-page2',
            'FunctionArn' => 'arn:aws:lambda:eu-west-1:123:function:func-page2',
        ];

        $function1 = new LambdaFunction();
        $function1->setFunctionName('func-page1');
        $function1->setFunctionArn('arn:aws:lambda:eu-west-1:123:function:func-page1');

        $function2 = new LambdaFunction();
        $function2->setFunctionName('func-page2');
        $function2->setFunctionArn('arn:aws:lambda:eu-west-1:123:function:func-page2');

        $this->denormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($function1, $function2);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $page1 = new Result([
            'Functions' => [$functionData1],
            'NextMarker' => 'marker-page-2',
        ]);

        $page2 = new Result([
            'Functions' => [$functionData2],
            'NextMarker' => null,
        ]);

        $crawler = $this->createCrawlerWithMockClient([
            'listFunctions' => [$page1, $page2],
            'listTags' => new Result(['Tags' => []]),
        ]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'eu-west-1', '123', new CrawlVersion());

        $this->assertNotNull($function1->getCrawl());
        $this->assertNotNull($function2->getCrawl());
    }

    public function testCrawlSkipsTagsWhenFunctionArnIsNull(): void
    {
        $functionData = [
            'FunctionName' => 'no-arn-function',
        ];

        $lambdaFunction = new LambdaFunction();
        $lambdaFunction->setFunctionName('no-arn-function');
        // FunctionArn is null

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->with($functionData, LambdaFunction::class, null, [
                'object_context' => LambdaFunction::class,
            ])
            ->willReturn($lambdaFunction);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($lambdaFunction);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $listFunctionsResult = new Result([
            'Functions' => [$functionData],
            'NextMarker' => null,
        ]);

        // listTags should never be called since ARN is null
        $crawler = $this->createCrawlerWithMockClient([
            'listFunctions' => [$listFunctionsResult],
            'listTags' => null, // will throw if called
        ]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'eu-west-1', '123', new CrawlVersion());

        $this->assertCount(0, $lambdaFunction->getTags());
    }

    public function testCrawlContinuesWhenTagAccessDenied(): void
    {
        $functionData = [
            'FunctionName' => 'denied-tags-function',
            'FunctionArn' => 'arn:aws:lambda:eu-west-1:123:function:denied-tags-function',
        ];

        $lambdaFunction = new LambdaFunction();
        $lambdaFunction->setFunctionName('denied-tags-function');
        $lambdaFunction->setFunctionArn('arn:aws:lambda:eu-west-1:123:function:denied-tags-function');

        $this->denormalizer->expects($this->once())
            ->method('denormalize')
            ->willReturn($lambdaFunction);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($lambdaFunction);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $listFunctionsResult = new Result([
            'Functions' => [$functionData],
            'NextMarker' => null,
        ]);

        // listTags will throw an exception - crawler should continue
        $crawler = $this->createCrawlerWithMockClient([
            'listFunctions' => [$listFunctionsResult],
            'listTags' => new \RuntimeException('AccessDeniedException'),
        ]);
        $crawler->crawl(new Credentials('k', 's', 't'), 'eu-west-1', '123', new CrawlVersion());

        $this->assertCount(0, $lambdaFunction->getTags());
    }

    /**
     * @param array $calls Keyed by method name: 'listFunctions' => Result[], 'listTags' => Result|\Exception|null
     */
    private function createCrawlerWithMockClient(array $calls): AWSLambdaCrawler
    {
        $lambdaClient = $this->createMock(LambdaClient::class);
        $listFunctionsCallIndex = 0;

        $lambdaClient->method('__call')
            ->willReturnCallback(function (string $method, array $args) use ($calls, &$listFunctionsCallIndex) {
                return match ($method) {
                    'listFunctions' => $calls['listFunctions'][$listFunctionsCallIndex++],
                    'listTags' => $calls['listTags'] instanceof \Exception
                        ? throw $calls['listTags']
                        : ($calls['listTags'] ?? throw new \RuntimeException('listTags should not be called')),
                    default => new Result([]),
                };
            });

        $entityManager = $this->entityManager;
        $denormalizer = $this->denormalizer;

        return new class(
            $this->createMock(ManagerRegistry::class),
            $entityManager,
            $this->createMock(SerializerInterface::class),
            $denormalizer,
            $lambdaClient,
        ) extends AWSLambdaCrawler {
            private LambdaClient $mockClient;

            public function __construct(
                \Doctrine\Persistence\ManagerRegistry $registry,
                \Doctrine\ORM\EntityManagerInterface $entityManager,
                \Symfony\Component\Serializer\SerializerInterface $serializer,
                \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $denormalizer,
                LambdaClient $mockClient,
            ) {
                parent::__construct($registry, $entityManager, $serializer, $denormalizer);
                $this->mockClient = $mockClient;
            }

            protected function createLambdaClient(Credentials $credentials, string $regionName): LambdaClient
            {
                return $this->mockClient;
            }
        };
    }
}
