<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\SiteInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SiteInventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteInventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteInventory[]    findAll()
 * @method SiteInventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteInventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteInventory::class);
    }

    /**
     * @param Site $site
     * @param $ids
     */
    public function removeBySiteNotIn(Site $site, $ids){
        $sql = "DELETE FROM App:SiteInventory si WHERE si.site=:site";
        if(!empty($ids)){
            $sql .= " AND si.id NOT IN (:ids)";
        }
        $query =  $this->getEntityManager()
            ->createQuery($sql)
            ->setParameter('site', $site->getId());

        if(!empty($ids)){
            $query->setParameter('ids', $ids);
        }

        $query->getResult();
    }
}
