<?php

namespace App\Repository;

use App\Entity\CPIRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CPIRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method CPIRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method CPIRate[]    findAll()
 * @method CPIRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CPIRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CPIRate::class);
    }

    // /**
    //  * @return CPIRate[] Returns an array of CPIRate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CPIRate
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
