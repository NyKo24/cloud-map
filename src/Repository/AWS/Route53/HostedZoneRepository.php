<?php

namespace App\Repository\AWS\Route53;

use App\Entity\AWS\Route53\HostedZone;
use App\Search\HostedZoneListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HostedZone>
 */
class HostedZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HostedZone::class);
    }

    public function listHostedZonesForUser(HostedZoneListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('hz')
            ->select('hz, cv')
            ->join('hz.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('hz.name', 'ASC');

        if ($search->name) {
            $qb->andWhere('hz.name LIKE :name')
                ->setParameter('name', '%' . $search->name . '%');
        }

        if ($search->hostedZoneId) {
            $qb->andWhere('hz.hostedZoneId LIKE :hostedZoneId')
                ->setParameter('hostedZoneId', '%' . $search->hostedZoneId . '%');
        }

        if ($search->privateZone !== null) {
            $qb->andWhere('hz.privateZone = :privateZone')
                ->setParameter('privateZone', $search->privateZone);
        }

        return $qb;
    }
}
