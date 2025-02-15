<?php

namespace App\Repository;

use App\Entity\LeaseOtherUtilityCostCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseOtherUtilityCostCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseOtherUtilityCostCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseOtherUtilityCostCategory[]    findAll()
 * @method LeaseOtherUtilityCostCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseOtherUtilityCostCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseOtherUtilityCostCategory::class);
    }

    // /**
    //  * @return LeaseOtherUtilityCostCategory[] Returns an array of LeaseOtherUtilityCostCategory objects
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
    public function findOneBySomeField($value): ?LeaseOtherUtilityCostCategory
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
