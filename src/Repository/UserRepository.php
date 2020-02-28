<?php


namespace App\Repository;


use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getAll($params=[]){
        $query = $this->createQueryBuilder('u')
            ->select('u');

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search) OR (u.email LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['type']) && !empty($params['type'])){
            $query->andWhere('u.type = :type')
                ->setParameter('type', $params['type']);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $role
     * @return mixed
     */
    public function findByRole($role)
    {
        return $this->_em->createQueryBuilder('u')
            ->select('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%')
            ->getQuery()
            ->getResult();
    }
}
