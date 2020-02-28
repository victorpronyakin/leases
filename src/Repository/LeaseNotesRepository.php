<?php

namespace App\Repository;

use App\Entity\Lease;
use App\Entity\LeaseNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LeaseNotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method LeaseNotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method LeaseNotes[]    findAll()
 * @method LeaseNotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseNotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaseNotes::class);
    }

    /**
     * @param Lease $lease
     * @param $ids
     */
    public function removeByLeaseInNot(Lease $lease, $ids){
        $sql = "DELETE FROM App:LeaseNotes ln WHERE ln.lease = :lease";
        if(!empty($ids)){
            $sql .= " AND ln.id NOT IN (:ids)";
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
