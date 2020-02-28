<?php

namespace App\Repository;

use App\Entity\Landlord;
use App\Entity\LandlordComments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LandlordComments|null find($id, $lockMode = null, $lockVersion = null)
 * @method LandlordComments|null findOneBy(array $criteria, array $orderBy = null)
 * @method LandlordComments[]    findAll()
 * @method LandlordComments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordCommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LandlordComments::class);
    }

    /**
     * @param Landlord $landlord
     * @param $ids
     */
    public function removeByLandlordNotIn(Landlord $landlord, $ids){
        $sql = "DELETE FROM App:LandlordComments lc WHERE lc.landlord=:landlord";
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
