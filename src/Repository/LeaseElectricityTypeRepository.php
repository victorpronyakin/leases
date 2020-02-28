<?php

namespace App\Repository;

use App\Entity\LeaseElectricityType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseElectricityType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseElectricityType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseElectricityType[]    findAll()
 * @method LeaseElectricityType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseElectricityTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseElectricityType::class);
    }

    // /**
    //  * @return LeaseElectricityType[] Returns an array of LeaseElectricityType objects
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
    public function findOneBySomeField($value): ?LeaseElectricityType
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
