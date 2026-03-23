<?php

namespace App\Repository\AWS\VPC;

use App\Entity\AWS\VPC\VPC;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VPC>
 */
class VPCRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VPC::class);
    }

}
