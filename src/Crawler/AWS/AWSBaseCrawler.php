<?php

namespace App\Crawler\AWS;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AWSBaseCrawler implements AWSCrawlerInterface
{
    public function __construct(
        protected ManagerRegistry $registry,
        protected EntityManagerInterface $entityManager,
        protected SerializerInterface $serializer,
        protected DenormalizerInterface $denormalizer
    )
    {}

    public function isGlobal(): bool
    {
        return false;
    }
}
