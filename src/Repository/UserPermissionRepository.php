<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPermission[]    findAll()
 * @method UserPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPermission::class);
    }

    /**
     * @param User $user
     */
    public function removePermissionsByUser(User $user){

        $this->getEntityManager()
            ->createQuery(
                "DELETE FROM App:UserPermission p WHERE p.user=:user"
            )
            ->setParameter('user', $user->getId())
            ->getResult();
    }
}
