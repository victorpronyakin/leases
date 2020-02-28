<?php

namespace App\Repository;

use App\Entity\ActualReminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ActualReminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActualReminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActualReminder[]    findAll()
 * @method ActualReminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActualReminderRepository extends ServiceEntityRepository
{
    /**
     * ActualReminderRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActualReminder::class);
    }

    /**
     * @param $status
     * @param bool $dashboard
     * @param bool $snoozed
     * @return mixed
     * @throws \Exception
     */
    public function getAllByParams($status, $dashboard=false, $snoozed = false){
        $now = new \DateTime();

        $query = $this->createQueryBuilder('ar')
            ->select('ar')
            ->from('App:Reminder', 'r')
            ->where('ar.reminder = r.id')
            ->andWhere('ar.status = :status')
            ->setParameter('status', $status);

        if($dashboard){
            $query->andWhere('r.dashboard = :dashboard')
                ->setParameter('dashboard', $dashboard);
        }

        if($snoozed){
            $query->andWhere("ar.snoozeDate IS NOT NULL")
                ->andWhere("DATE_FORMAT(ar.snoozeDate, '%Y-%m-%d') > :now")
                ->setParameter('now', $now->format('Y-m-d'));
        }
        else{
            $query->andWhere("(ar.snoozeDate IS NULL OR DATE_FORMAT(ar.snoozeDate, '%Y-%m-%d') <= :now)")
                ->setParameter('now', $now->format('Y-m-d'));
        }


        return $query->getQuery()->getResult();
    }
}
