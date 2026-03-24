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

    public function listSecurityGroupsWithRuleCountsForUser(SecurityGroupListSearch $search): QueryBuilder
    {
        return $this->listSecurityGroupsForUser($search)
            ->addSelect('(SELECT COUNT(ri.id) FROM App\Entity\AWS\EC2\SecurityGroupRule ri WHERE ri.securityGroup = sg AND ri.direction = :ingress) AS ingressCount')
            ->addSelect('(SELECT COUNT(re.id) FROM App\Entity\AWS\EC2\SecurityGroupRule re WHERE re.securityGroup = sg AND re.direction = :egress) AS egressCount')
            ->setParameter('ingress', 'ingress')
            ->setParameter('egress', 'egress');
    }
}
