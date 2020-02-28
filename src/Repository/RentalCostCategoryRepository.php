<?php

namespace App\Repository;

use App\Entity\RentalCostCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RentalCostCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method RentalCostCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method RentalCostCategory[]    findAll()
 * @method RentalCostCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RentalCostCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentalCostCategory::class);
    }

    // /**
    //  * @return RentalCostCategory[] Returns an array of RentalCostCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RentalCostCategory
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
