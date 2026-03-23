<?php

namespace App\Repository\AWS\VPC;

use App\Entity\AWS\VPC\VpcCidrBlockAssociation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpcCidrBlockAssociation>
 */
class VpcCidrBlockAssociationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpcCidrBlockAssociation::class);
    }

}
