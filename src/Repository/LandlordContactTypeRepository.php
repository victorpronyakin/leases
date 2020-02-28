<?php

namespace App\Repository;

use App\Entity\LandlordContactType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordContactType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordContactType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordContactType[]    findAll()
 * @method LandlordContactType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordContactTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordContactType::class);
    }

    // /**
    //  * @return LandlordContactType[] Returns an array of LandlordContactType objects
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
    public function findOneBySomeField($value): ?LandlordContactType
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
