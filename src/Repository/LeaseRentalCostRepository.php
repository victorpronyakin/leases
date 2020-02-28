<?php

namespace App\Repository;

use App\Entity\LeaseRentalCost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseRentalCost|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseRentalCost|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseRentalCost[]    findAll()
 * @method LeaseRentalCost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseRentalCostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseRentalCost::class);
    }

    // /**
    //  * @return LeaseRentalCost[] Returns an array of LeaseRentalCost objects
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
    public function findOneBySomeField($value): ?LeaseRentalCost
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
