<?php

namespace App\Repository\AWS\RDS;

use App\Entity\AWS\RDS\RdsInstance;
use App\Search\RdsInstanceListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RdsInstance>
 */
class RdsInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RdsInstance::class);
    }

    public function listRdsInstancesForUser(RdsInstanceListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ri')
            ->select('ri, cv')
            ->join('ri.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('ri.dbInstanceIdentifier', 'ASC');

        if ($search->dbInstanceIdentifier) {
            $qb->andWhere('ri.dbInstanceIdentifier LIKE :dbInstanceIdentifier')
                ->setParameter('dbInstanceIdentifier', '%' . $search->dbInstanceIdentifier . '%');
        }

        if ($search->engine) {
            $qb->andWhere('ri.engine = :engine')
                ->setParameter('engine', $search->engine);
        }

        if ($search->dbInstanceStatus) {
            $qb->andWhere('ri.dbInstanceStatus = :dbInstanceStatus')
                ->setParameter('dbInstanceStatus', $search->dbInstanceStatus);
        }

        if ($search->dbInstanceClass) {
            $qb->andWhere('ri.dbInstanceClass = :dbInstanceClass')
                ->setParameter('dbInstanceClass', $search->dbInstanceClass);
        }

        return $qb;
    }
}
