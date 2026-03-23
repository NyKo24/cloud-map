<?php

namespace App\Repository\AWS\Lambda;

use App\Entity\AWS\Lambda\VpcConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpcConfig>
 */
class VpcConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpcConfig::class);
    }
}