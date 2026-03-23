<?php

namespace App\Repository\AWS\Lambda;

use App\Entity\AWS\Lambda\LambdaFunction;
use App\Search\LambdaFunctionListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LambdaFunction>
 */
class LambdaFunctionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LambdaFunction::class);
    }

    public function listLambdaFunctionsForUser(LambdaFunctionListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('lf')
            ->select('lf, cv, aa, c')
            ->join('lf.crawl', 'cv')
            ->join('cv.awsAccount', 'aa')
            ->join('aa.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('lf.functionName', 'ASC');

        if ($search->functionName) {
            $qb->andWhere('lf.functionName LIKE :functionName')
                ->setParameter('functionName', '%' . $search->functionName . '%');
        }

        if ($search->functionArn) {
            $qb->andWhere('lf.functionArn LIKE :functionArn')
                ->setParameter('functionArn', '%' . $search->functionArn . '%');
        }

        if ($search->runtime) {
            $qb->andWhere('lf.runtime = :runtime')
                ->setParameter('runtime', $search->runtime);
        }

        if ($search->state) {
            $qb->andWhere('lf.state = :state')
                ->setParameter('state', $search->state);
        }

        if ($search->packageType) {
            $qb->andWhere('lf.packageType = :packageType')
                ->setParameter('packageType', $search->packageType);
        }

        return $qb;
    }
}