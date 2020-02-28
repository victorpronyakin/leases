<?php

namespace App\Repository;

use App\Entity\LandlordType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordType[]    findAll()
 * @method LandlordType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordType::class);
    }

    // /**
    //  * @return LandlordType[] Returns an array of LandlordType objects
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
    public function findOneBySomeField($value): ?LandlordType
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
