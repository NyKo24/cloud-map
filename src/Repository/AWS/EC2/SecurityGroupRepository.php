<?php

namespace App\Repository\AWS\EC2;

use App\Entity\AWS\EC2\SecurityGroup;
use App\Search\SecurityGroupListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecurityGroup>
 */
class SecurityGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityGroup::class);
    }

    public function findOneForUser(int $id, int $userId): ?SecurityGroup
    {
        return $this->createQueryBuilder('sg')
            ->join('sg.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('sg.id = :id')
            ->andWhere('u.id = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function listSecurityGroupsForUser(SecurityGroupListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('sg')
            ->select('sg, cv')
            ->join('sg.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('sg.groupId', 'ASC');

        if ($search->groupId) {
            $qb->andWhere('sg.groupId LIKE :groupId')
                ->setParameter('groupId', '%' . $search->groupId . '%');
        }

        if ($search->groupName) {
            $qb->andWhere('sg.groupName LIKE :groupName')
                ->setParameter('groupName', '%' . $search->groupName . '%');
        }

        if ($search->vpcId) {
            $qb->andWhere('sg.vpcId LIKE :vpcId')
                ->setParameter('vpcId', '%' . $search->vpcId . '%');
        }

        return $qb;
    }
}
