<?php

namespace App\Repository\AWS\ELB;

use App\Entity\AWS\ELB\LoadBalancer;
use App\Search\LoadBalancerListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoadBalancer>
 */
class LoadBalancerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoadBalancer::class);
    }

    public function listLoadBalancersForUser(LoadBalancerListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('lb')
            ->select('lb, cv')
            ->join('lb.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('lb.loadBalancerName', 'ASC');

        if ($search->loadBalancerName) {
            $qb->andWhere('lb.loadBalancerName LIKE :loadBalancerName')
                ->setParameter('loadBalancerName', '%' . $search->loadBalancerName . '%');
        }

        if ($search->type) {
            $qb->andWhere('lb.type = :type')
                ->setParameter('type', $search->type);
        }

        if ($search->scheme) {
            $qb->andWhere('lb.scheme = :scheme')
                ->setParameter('scheme', $search->scheme);
        }

        return $qb;
    }
}
