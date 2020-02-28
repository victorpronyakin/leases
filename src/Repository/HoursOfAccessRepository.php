<?php

namespace App\Repository;

use App\Entity\HoursOfAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HoursOfAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoursOfAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoursOfAccess[]    findAll()
 * @method HoursOfAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoursOfAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoursOfAccess::class);
    }

    // /**
    //  * @return HoursOfAccess[] Returns an array of HoursOfAccess objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HoursOfAccess
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
