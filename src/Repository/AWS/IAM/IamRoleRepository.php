<?php

namespace App\Repository\AWS\IAM;

use App\Entity\AWS\IAM\IamRole;
use App\Search\IamRoleListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IamRole>
 */
class IamRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IamRole::class);
    }

    public function listIamRolesForUser(IamRoleListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r, cv')
            ->join('r.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('r.roleName', 'ASC');

        if ($search->roleName) {
            $qb->andWhere('r.roleName LIKE :roleName')
                ->setParameter('roleName', '%' . $search->roleName . '%');
        }

        if ($search->path) {
            $qb->andWhere('r.path LIKE :path')
                ->setParameter('path', '%' . $search->path . '%');
        }

        if ($search->arn) {
            $qb->andWhere('r.arn LIKE :arn')
                ->setParameter('arn', '%' . $search->arn . '%');
        }

        return $qb;
    }
}
