<?php

namespace App\Repository\AWS\EC2;

use App\Entity\AWS\EC2\SecurityGroupRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecurityGroupRule>
 */
class SecurityGroupRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityGroupRule::class);
    }
}
