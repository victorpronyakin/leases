<?php

namespace App\Repository;

use App\Entity\Landlord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Landlord|null find($id, $lockMode = null, $lockVersion = null)
 * @method Landlord|null findOneBy(array $criteria, array $orderBy = null)
 * @method Landlord[]    findAll()
 * @method Landlord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LandlordRepository extends ServiceEntityRepository
{
    /**
     * LandlordRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Landlord::class);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function findByParamsForReminders($params=array()){
        $query = $this->createQueryBuilder('l')->select("l");

        if(isset($params['documentStatus']) && !empty($params['documentStatus'])){
            $query->andWhere('l.documentStatus = :documentStatus')
                ->setParameter('documentStatus', $params['documentStatus']);
        }
        if(isset($params['documentStatusUpdated']) && !empty($params['documentStatusUpdated'])){
            $query->andWhere('DATEDIFF(NOW(), l.documentStatusUpdated) = :documentStatusUpdated')
                ->setParameter('documentStatusUpdated', $params['documentStatusUpdated']);
        }

        return $query->getQuery()->getResult();
    }
}
