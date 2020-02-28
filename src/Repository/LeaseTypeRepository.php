<?php

namespace App\Repository;

use App\Entity\LeaseType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseType[]    findAll()
 * @method LeaseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseType::class);
    }

    // /**
    //  * @return LeaseType[] Returns an array of LeaseType objects
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
    public function findOneBySomeField($value): ?LeaseType
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
