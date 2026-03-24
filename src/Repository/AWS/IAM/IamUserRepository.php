<?php

namespace App\Repository\AWS\IAM;

use App\Entity\AWS\IAM\IamUser;
use App\Search\IamUserListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IamUser>
 */
class IamUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IamUser::class);
    }

    public function listIamUsersForUser(IamUserListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('iu')
            ->select('iu, cv')
            ->join('iu.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('iu.userName', 'ASC');

        if ($search->userName) {
            $qb->andWhere('iu.userName LIKE :userName')
                ->setParameter('userName', '%' . $search->userName . '%');
        }

        if ($search->path) {
            $qb->andWhere('iu.path LIKE :path')
                ->setParameter('path', '%' . $search->path . '%');
        }

        if ($search->arn) {
            $qb->andWhere('iu.arn LIKE :arn')
                ->setParameter('arn', '%' . $search->arn . '%');
        }

        return $qb;
    }
}
