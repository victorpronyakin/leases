<?php

namespace App\Repository;

use App\Entity\Lease;
use App\Entity\LeaseDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseDocument[]    findAll()
 * @method LeaseDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseDocumentRepository extends ServiceEntityRepository
{
    /**
     * LeaseDocumentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseDocument::class);
    }

    /**
     * @param Lease $lease
     * @param $ids
     */
    public function removeByLeaseInNot(Lease $lease, $ids){
        $sql = "DELETE FROM App:LeaseDocument ld WHERE ld.lease = :lease";
        if(!empty($ids)){
            $sql .= " AND ld.id NOT IN (:ids)";
        }
        $query = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('lease', $lease->getId());

        if(!empty($ids)){
            $query->setParameter('ids', $ids);
        }
        $query->getResult();
    }
}
