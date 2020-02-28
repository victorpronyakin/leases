<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    /**
     * SiteRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function findByParamsForReminders($params=array()){
        $query = $this->createQueryBuilder('s')->select("s");

        if(isset($params['siteStatus']) && !empty($params['siteStatus'])){
            $query->andWhere('s.siteStatus = :siteStatus')
                ->setParameter('siteStatus', $params['siteStatus']);
        }

        if(isset($params['siteStatusUpdated']) && !empty($params['siteStatusUpdated'])){
            $query->andWhere('DATEDIFF(NOW(), s.siteStatusUpdated) = :siteStatusUpdated')
                ->setParameter('siteStatusUpdated', $params['siteStatusUpdated']);
        }

        return $query->getQuery()->getResult();
    }
}
