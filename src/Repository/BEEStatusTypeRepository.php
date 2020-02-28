<?php

namespace App\Repository;

use App\Entity\BEEStatusType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BEEStatusType|null find($id, $lockMode = null, $lockVersion = null)
 * @method BEEStatusType|null findOneBy(array $criteria, array $orderBy = null)
 * @method BEEStatusType[]    findAll()
 * @method BEEStatusType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BEEStatusTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BEEStatusType::class);
    }

    // /**
    //  * @return BEEStatusType[] Returns an array of BEEStatusType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BEEStatusType
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
