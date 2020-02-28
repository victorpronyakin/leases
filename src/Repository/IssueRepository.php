<?php

namespace App\Repository;

use App\Entity\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Issue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Issue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Issue[]    findAll()
 * @method Issue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueRepository extends ServiceEntityRepository
{
    /**
     * IssueRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function findByParamsForReminders($params=array()){
        $query = $this->createQueryBuilder('i')
            ->select("i")
            ->where('i.status = :true')
            ->setParameter('true', true);

        if(isset($params['logged']) && !empty($params['logged'])){
            $query->andWhere('DATEDIFF(NOW(), i.logged) = :logged')
                ->setParameter('logged', $params['logged']);
        }


        return $query->getQuery()->getResult();
    }
}
