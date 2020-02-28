<?php

namespace App\Repository;

use App\Entity\Landlord;
use App\Entity\LandlordDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordDocument[]    findAll()
 * @method LandlordDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordDocumentRepository extends ServiceEntityRepository
{
    /**
     * LandlordDocumentRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordDocument::class);
    }

    /**
     * @param Landlord $landlord
     * @param $ids
     */
    public function removeByLandlordInNot(Landlord $landlord, $ids){
        $sql = "DELETE FROM App:LandlordDocument ld WHERE ld.landlord = :landlord";
        if(!empty($ids)){
            $sql .= " AND ld.id NOT IN (:ids)";
        }
        $query = $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('landlord', $landlord->getId());

        if(!empty($ids)){
            $query->setParameter('ids', $ids);
        }
        $query->getResult();
    }
}
