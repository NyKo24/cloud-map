<?php

namespace App\Repository\AWS;

use App\Entity\AWS\AwsAccount;
use App\Entity\User;
use App\Search\AWSAccountListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AwsAccount>
 */
class AwsAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwsAccount::class);
    }

    public function listAccountForAllUserCustomers(AWSAccountListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a, c')
            ->join('a.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId);

        if ($search->status) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $search->status);
        }

        if ($search->accountName) {
            $qb->andWhere('a.name LIKE :accountName')
                ->setParameter('accountName', '%' . $search->accountName . '%');
        }

        if ($search->email) {
            $qb->andWhere('a.email LIKE :email')
                ->setParameter('email', '%' . $search->email . '%');
        }

        if ($search->associationMethod) {
            $qb->andWhere('a.joinedMethod = :associationMethod')
                ->setParameter('associationMethod', $search->associationMethod);
        }

        return $qb;
    }
}
