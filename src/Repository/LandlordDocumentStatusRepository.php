<?php

namespace App\Repository;

use App\Entity\LandlordDocumentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordDocumentStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordDocumentStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordDocumentStatus[]    findAll()
 * @method LandlordDocumentStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordDocumentStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordDocumentStatus::class);
    }

    // /**
    //  * @return LandlordDocumentStatus[] Returns an array of LandlordDocumentStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LandlordDocumentStatus
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
