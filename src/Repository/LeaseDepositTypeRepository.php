<?php

namespace App\Repository;

use App\Entity\LeaseDepositType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseDepositType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseDepositType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseDepositType[]    findAll()
 * @method LeaseDepositType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseDepositTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseDepositType::class);
    }

    // /**
    //  * @return LeaseDepositType[] Returns an array of LeaseDepositType objects
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
    public function findOneBySomeField($value): ?LeaseDepositType
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
