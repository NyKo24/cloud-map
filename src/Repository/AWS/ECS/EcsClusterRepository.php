<?php

namespace App\Repository\AWS\ECS;

use App\Entity\AWS\ECS\EcsCluster;
use App\Search\EcsClusterListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EcsCluster>
 */
class EcsClusterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EcsCluster::class);
    }

    public function listEcsClustersForUser(EcsClusterListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ecs')
            ->select('ecs, cv')
            ->join('ecs.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('ecs.clusterName', 'ASC');

        if ($search->clusterName) {
            $qb->andWhere('ecs.clusterName LIKE :clusterName')
                ->setParameter('clusterName', '%' . $search->clusterName . '%');
        }

        if ($search->status) {
            $qb->andWhere('ecs.status = :status')
                ->setParameter('status', $search->status);
        }

        return $qb;
    }
}
