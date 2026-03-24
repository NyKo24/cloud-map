<?php

namespace App\Repository\AWS\EC2;

use App\Entity\AWS\EC2\Ec2Instance;
use App\Search\Ec2InstanceListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ec2Instance>
 */
class Ec2InstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ec2Instance::class);
    }

    public function listEc2InstancesForUser(Ec2InstanceListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ei')
            ->select('ei, cv')
            ->join('ei.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('ei.instanceId', 'ASC');

        if ($search->instanceId) {
            $qb->andWhere('ei.instanceId LIKE :instanceId')
                ->setParameter('instanceId', '%' . $search->instanceId . '%');
        }

        if ($search->instanceType) {
            $qb->andWhere('ei.instanceType = :instanceType')
                ->setParameter('instanceType', $search->instanceType);
        }

        if ($search->state) {
            $qb->andWhere('ei.state LIKE :state')
                ->setParameter('state', '%"Name":"' . $search->state . '"%');
        }

        if ($search->vpcId) {
            $qb->andWhere('ei.vpcId LIKE :vpcId')
                ->setParameter('vpcId', '%' . $search->vpcId . '%');
        }

        if ($search->architecture) {
            $qb->andWhere('ei.architecture = :architecture')
                ->setParameter('architecture', $search->architecture);
        }

        return $qb;
    }
}
