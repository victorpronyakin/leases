<?php

namespace App\Repository;

use App\Entity\LandlordDocumentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordDocumentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordDocumentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordDocumentType[]    findAll()
 * @method LandlordDocumentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordDocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordDocumentType::class);
    }

    // /**
    //  * @return LandlordDocumentType[] Returns an array of LandlordDocumentType objects
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
    public function findOneBySomeField($value): ?LandlordDocumentType
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
