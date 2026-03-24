<?php

namespace App\Repository\AWS\CloudWatch;

use App\Entity\AWS\CloudWatch\CloudWatchAlarm;
use App\Search\CloudWatchAlarmListSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CloudWatchAlarm>
 */
class CloudWatchAlarmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CloudWatchAlarm::class);
    }

    public function listCloudWatchAlarmsForUser(CloudWatchAlarmListSearch $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('cwa')
            ->select('cwa, cv')
            ->join('cwa.crawl', 'cv')
            ->join('cv.customer', 'c')
            ->innerJoin('c.users', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $search->userId)
            ->orderBy('cwa.alarmName', 'ASC');

        if ($search->alarmName) {
            $qb->andWhere('cwa.alarmName LIKE :alarmName')
                ->setParameter('alarmName', '%' . $search->alarmName . '%');
        }

        if ($search->stateValue) {
            $qb->andWhere('cwa.stateValue = :stateValue')
                ->setParameter('stateValue', $search->stateValue);
        }

        if ($search->metricName) {
            $qb->andWhere('cwa.metricName LIKE :metricName')
                ->setParameter('metricName', '%' . $search->metricName . '%');
        }

        if ($search->namespace) {
            $qb->andWhere('cwa.namespace LIKE :namespace')
                ->setParameter('namespace', '%' . $search->namespace . '%');
        }

        if ($search->actionsEnabled !== null) {
            $qb->andWhere('cwa.actionsEnabled = :actionsEnabled')
                ->setParameter('actionsEnabled', $search->actionsEnabled);
        }

        return $qb;
    }
}
