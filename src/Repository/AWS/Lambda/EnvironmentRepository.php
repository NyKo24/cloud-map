<?php

namespace App\Repository\AWS\Lambda;

use App\Entity\AWS\Lambda\Environment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Environment>
 */
class EnvironmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Environment::class);
    }
}