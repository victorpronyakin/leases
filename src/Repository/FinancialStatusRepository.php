<?php

namespace App\Repository;

use App\Entity\FinancialStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FinancialStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method FinancialStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method FinancialStatus[]    findAll()
 * @method FinancialStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinancialStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialStatus::class);
    }

    // /**
    //  * @return FinancialStatus[] Returns an array of FinancialStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FinancialStatus
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
