<?php

namespace App\Repository\AWS\S3;

use App\Entity\AWS\S3\S3Bucket;
use App\Search\S3BucketListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<S3Bucket>
 */
class S3BucketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, S3Bucket::class);
    }

    public function listS3BucketsForUser(S3BucketListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s3')
            ->select('s3, cv')
            ->join('s3.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('s3.name', 'ASC');

        if ($search->name) {
            $qb->andWhere('s3.name LIKE :name')
                ->setParameter('name', '%' . $search->name . '%');
        }

        if ($search->region) {
            $qb->andWhere('s3.region = :region')
                ->setParameter('region', $search->region);
        }

        if ($search->versioningStatus) {
            $qb->andWhere('s3.versioningStatus = :versioningStatus')
                ->setParameter('versioningStatus', $search->versioningStatus);
        }

        if ($search->encryptionEnabled !== null) {
            $qb->andWhere('s3.encryptionEnabled = :encryptionEnabled')
                ->setParameter('encryptionEnabled', $search->encryptionEnabled);
        }

        return $qb;
    }
}
