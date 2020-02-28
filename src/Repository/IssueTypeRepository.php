<?php

namespace App\Repository;

use App\Entity\IssueType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method IssueType|null find($id, $lockMode = null, $lockVersion = null)
 * @method IssueType|null findOneBy(array $criteria, array $orderBy = null)
 * @method IssueType[]    findAll()
 * @method IssueType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueType::class);
    }

    // /**
    //  * @return IssueType[] Returns an array of IssueType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IssueType
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
