<?php

namespace App\Repository;

use App\Entity\LeaseDocumentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseDocumentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseDocumentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseDocumentType[]    findAll()
 * @method LeaseDocumentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseDocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseDocumentType::class);
    }

    // /**
    //  * @return LeaseDocumentType[] Returns an array of LeaseDocumentType objects
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
    public function findOneBySomeField($value): ?LeaseDocumentType
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
