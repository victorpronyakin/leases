<?php

namespace App\Repository;

use App\Entity\ManagementStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ManagementStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagementStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagementStatus[]    findAll()
 * @method ManagementStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagementStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ManagementStatus::class);
    }


}
