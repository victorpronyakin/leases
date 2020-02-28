<?php

namespace App\Repository;

use App\Entity\Landlord;
use App\Entity\LandlordContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordContact[]    findAll()
 * @method LandlordContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordContactRepository extends ServiceEntityRepository
{
    /**
     * LandlordContactRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordContact::class);
    }

    /**
     * @param Landlord $landlord
     * @param $ids
     */
    public function removeByLandlordNotIn(Landlord $landlord, $ids){
        $sql = "DELETE FROM App:LandlordContact lc WHERE lc.landlord=:landlord";
        if(!empty($ids)){
            $sql .= " AND lc.id NOT IN (:ids)";
        }
        $query =  $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('landlord', $landlord->getId());

        if(!empty($ids)){
            $query->setParameter('ids', $ids);
        }

        $query->getResult();
    }
}
