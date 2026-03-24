<?php

namespace App\Repository\AWS\EKS;

use App\Entity\AWS\EKS\EksCluster;
use App\Search\EksClusterListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EksCluster>
 */
class EksClusterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EksCluster::class);
    }

    public function listEksClustersForUser(EksClusterListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('eks')
            ->select('eks, cv')
            ->join('eks.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('eks.name', 'ASC');

        if ($search->name) {
            $qb->andWhere('eks.name LIKE :name')
                ->setParameter('name', '%' . $search->name . '%');
        }

        if ($search->status) {
            $qb->andWhere('eks.status = :status')
                ->setParameter('status', $search->status);
        }

        if ($search->version) {
            $qb->andWhere('eks.version LIKE :version')
                ->setParameter('version', '%' . $search->version . '%');
        }

        return $qb;
    }
}
