<?php

namespace App\Repository\AWS\VPC;

use App\Entity\AWS\VPC\VpcIpv6CidrBlockAssociation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpcIpv6CidrBlockAssociation>
 */
class VpcIpv6CidrBlockAssociationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpcIpv6CidrBlockAssociation::class);
    }

}
