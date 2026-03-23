<?php

namespace App\Repository\AWS\Lambda;

use App\Entity\AWS\Lambda\Layer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Layer>
 */
class LayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Layer::class);
    }
}