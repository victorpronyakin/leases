<?php

namespace App\Repository;

use App\Entity\ContactReminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ContactReminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactReminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactReminder[]    findAll()
 * @method ContactReminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactReminderRepository extends ServiceEntityRepository
{
    /**
     * ContactReminderRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactReminder::class);
    }

    /**
     * @return mixed
     */
    public function findForSend(){

        return $this->createQueryBuilder('cr')
            ->select('cr')
            ->where('cr.status = :true')
            ->setParameter('true', true)
            ->andWhere("DATE_FORMAT(cr.dateNextSend, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')")
            ->getQuery()
            ->getResult();

    }
}
