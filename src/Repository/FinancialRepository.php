<?php

namespace App\Repository;

use App\Entity\Financial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Financial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Financial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Financial[]    findAll()
 * @method Financial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinancialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Financial::class);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function findByParamsForReminders($params=array()){
        $query = $this->createQueryBuilder('f')->select("f");

        if(isset($params['leasePaymentStatus']) && !empty($params['leasePaymentStatus'])){
            $query->andWhere('f.leasePaymentStatus = :leasePaymentStatus')
                ->setParameter('leasePaymentStatus', $params['leasePaymentStatus']);
        }
        if(isset($params['leasePaymentStatusUpdated']) && !empty($params['leasePaymentStatusUpdated'])){
            $query->andWhere('DATEDIFF(NOW(), f.leasePaymentStatusUpdated) = :leasePaymentStatusUpdated')
                ->setParameter('leasePaymentStatusUpdated', $params['leasePaymentStatusUpdated']);
        }

        if(isset($params['electricityPaymentStatus']) && !empty($params['electricityPaymentStatus'])){
            $query->andWhere('f.electricityPaymentStatus = :electricityPaymentStatus')
                ->setParameter('electricityPaymentStatus', $params['electricityPaymentStatus']);
        }
        if(isset($params['electricityPaymentStatusUpdated']) && !empty($params['electricityPaymentStatusUpdated'])){
            $query->andWhere('DATEDIFF(NOW(), f.electricityPaymentStatusUpdated) = :electricityPaymentStatusUpdated')
                ->setParameter('electricityPaymentStatusUpdated', $params['electricityPaymentStatusUpdated']);
        }

        if(isset($params['otherCostPaymentStatus']) && !empty($params['otherCostPaymentStatus'])){
            $query->andWhere('f.otherCostPaymentStatus = :otherCostPaymentStatus')
                ->setParameter('otherCostPaymentStatus', $params['otherCostPaymentStatus']);
        }
        if(isset($params['otherCostPaymentStatusUpdated']) && !empty($params['otherCostPaymentStatusUpdated'])){
            $query->andWhere('DATEDIFF(NOW(), f.otherCostPaymentStatusUpdated) = :otherCostPaymentStatusUpdated')
                ->setParameter('otherCostPaymentStatusUpdated', $params['otherCostPaymentStatusUpdated']);
        }

        return $query->getQuery()->getResult();
    }
}
