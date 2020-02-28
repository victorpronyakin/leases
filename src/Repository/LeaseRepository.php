<?php

namespace App\Repository;

use App\Entity\Lease;
use App\Entity\ManagementStatus;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Lease|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lease|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lease[]    findAll()
 * @method Lease[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lease::class);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function findAllWithParams($params=array()){

        $query = $this->createQueryBuilder('l')
            ->select('l')
            ->from('App:Site', 's')
            ->where('l.site = s.id');

        if(isset($params['date']) && !empty($params['date'])){
            $query->andWhere("DATE_FORMAT(l.startDate, '%Y-%m') <= DATE_FORMAT(:date, '%Y-%m')")
                ->andWhere("DATE_FORMAT(l.endDate, '%Y-%m') >= DATE_FORMAT(:date, '%Y-%m')")
                ->setParameter('date', $params['date']);
        }
        else{
            if(isset($params['endDate']) && !empty($params['endDate'])){
                $query->andWhere("DATE_FORMAT(l.endDate, '%Y-%m-%d') > DATE_FORMAT(NOW(), '%Y-%m-%d')");
            }
            else{
                $query->andWhere("DATE_FORMAT(l.startDate, '%Y-%m-%d') <= DATE_FORMAT(NOW(), '%Y-%m-%d')")
                    ->andWhere("DATE_FORMAT(l.endDate, '%Y-%m-%d') >= DATE_FORMAT(NOW(), '%Y-%m-%d')");
            }
        }

        if(isset($params['number']) && !empty($params['number'])){
            $query->andWhere('s.number LIKE :number')
                ->setParameter('number', '%'.$params['number'].'%');
        }
        if(isset($params['name']) && !empty($params['name'])){
            $query->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$params['name'].'%');
        }
        if(isset($params['status']) && !empty($params['status'])){
            $query->andWhere('s.siteStatus = :siteStatus')
                ->setParameter('siteStatus', $params['status']);
        }
        if(isset($params['expire']) && !empty($params['expire'])){
            $query->andWhere('DATEDIFF(l.endDate, NOW()) < :expire AND DATEDIFF(l.endDate, NOW()) > 0')
                ->setParameter('expire', $params['expire']);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $params
     * @return int
     */
    public function countAllWithParams($params=array()){
        $result = $this->findAllWithParams($params);
        return count($result);
    }

    /**
     * @param $params
     * @param $month
     * @return mixed
     * @throws \Exception
     */
    public function findAllForFinancialWithParams($params, $month)
    {
        $dateMonth = new \DateTime($month);
        $query = $this->createQueryBuilder('l')
            ->select('l')
            ->from('App:Site', 's')
            ->where('l.site = s.id')
            ->andWhere("DATE_FORMAT(l.startDate, '%Y-%m') <= :month")
            ->andWhere("DATE_FORMAT(l.endDate, '%Y-%m') >= :month")
            ->setParameter('month', $dateMonth->format('Y-m'))
        ;

        if(isset($params['number']) && !empty($params['number'])){
            $query
                ->andWhere('s.number LIKE :number')
                ->setParameter('number', '%'.$params['number'].'%');
        }

        if(isset($params['name']) && !empty($params['name'])){
            $query
                ->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$params['name'].'%');
        }

        if((isset($params['leasePaymentStatus']) && !empty($params['leasePaymentStatus']))
            || (isset($params['electricityPaymentStatus']) && !empty($params['electricityPaymentStatus']))
            || isset($params['otherCostPaymentStatus']) && !empty($params['otherCostPaymentStatus']))
        {
            $query
                ->from('App:Financial', 'f')
                ->andWhere('f.lease = l.id')
                ->andWhere('f.month = :finMonth')
                ->setParameter('finMonth', $month);
            if(isset($params['leasePaymentStatus']) && !empty($params['leasePaymentStatus'])){
                $query
                    ->andWhere('f.leasePaymentStatus = :leasePaymentStatus')
                    ->setParameter('leasePaymentStatus', $params['leasePaymentStatus']);
            }
            if(isset($params['electricityPaymentStatus']) && !empty($params['electricityPaymentStatus'])){
                $query
                    ->andWhere('f.electricityPaymentStatus = :electricityPaymentStatus')
                    ->setParameter('electricityPaymentStatus', $params['electricityPaymentStatus']);
            }
            if(isset($params['otherCostPaymentStatus']) && !empty($params['otherCostPaymentStatus'])){
                $query
                    ->andWhere('f.otherCostPaymentStatus = :otherCostPaymentStatus')
                    ->setParameter('otherCostPaymentStatus', $params['otherCostPaymentStatus']);
            }
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param Lease $lease
     * @return mixed
     * @throws \Exception
     */
    public function findPreviousByLease(Lease $lease){

        return $this->createQueryBuilder('l')
            ->select('l')
            ->where('l.site = :site')
            ->setParameter('site', $lease->getSite()->getId())
            ->andWhere('l.id != :id')
            ->setParameter('id', $lease->getId())
            ->andWhere("DATE_FORMAT(l.endDate, '%Y-%m-%d') < :now")
            ->setParameter('now', $lease->getStartDate()->format('Y-m-d'))
            ->orderBy('l.endDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Lease $lease
     * @return mixed|null
     * @throws \Exception
     */
    public function findOnePreviousByLease(Lease $lease){
        $previousLeases = $this->findPreviousByLease($lease);

        return (isset($previousLeases[0]) && $previousLeases[0] instanceof Lease) ? $previousLeases[0] : null;
    }


    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function findLeaseByAgents($params=[]){
        $now = new \DateTime();

        $query = $this->createQueryBuilder('l')
            ->select('l')
            ->from('App:Site', 's')
            ->where('l.site = s.id')
            ->andWhere("DATE_FORMAT(l.endDate, '%Y-%m-%d') > :now")
            ->setParameter('now', $now->format('Y-m-d'));

        $query->andWhere('l.agentStatus = :agentStatus');
        if(isset($params['agentStatus']) && is_bool($params['agentStatus'])){
            $query->setParameter('agentStatus', $params['agentStatus']);
        }
        else{
            $query->setParameter('agentStatus', true);
        }

        if(isset($params['allocated']) && !empty($params['allocated'])){
            $query->andWhere('l.allocated = :allocated')
                ->setParameter('allocated', $params['allocated']);
        }

        if(isset($params['number']) && !empty($params['number'])){
            $query->andWhere('s.number LIKE :number')
                ->setParameter('number', '%'.$params['number'].'%');
        }
        if(isset($params['name']) && !empty($params['name'])){
            $query->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$params['name'].'%');
        }
        if(isset($params['status']) && !empty($params['status'])){
            $query->andWhere('s.siteStatus = :siteStatus')
                ->setParameter('siteStatus', $params['status']);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param null $siteStatus
     * @return mixed
     * @throws \Exception
     */
    public function findCountLeaseByAgents($siteStatus=null){
        $result = $this->findLeaseByAgents(['status'=>$siteStatus]);

        return count($result);
    }

    /**
     * @param $startMonth
     * @param $endMonth
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function findAllForBillingWithParams($startMonth, $endMonth, $params = array()){
        $startMonth = new \DateTime($startMonth);
        $endMonth = new \DateTime($endMonth);
        $query = $this->createQueryBuilder('l')
            ->select('l')
            ->from('App:Site', 's')
            ->where('l.site = s.id')
            ->andWhere("NOT (DATE_FORMAT(l.startDate, '%Y-%m') > :endMonth OR DATE_FORMAT(l.endDate, '%Y-%m') < :startMonth )")
            ->setParameter('startMonth', $startMonth->format('Y-m'))
            ->setParameter('endMonth', $endMonth->format('Y-m'))
            ->andWhere('l.agentStatus = :agentStatus')
            ->setParameter('agentStatus', true)
        ;

        if(isset($params['number']) && !empty($params['number'])){
            $query
                ->andWhere('s.number LIKE :number')
                ->setParameter('number', '%'.$params['number'].'%');
        }

        if(isset($params['name']) && !empty($params['name'])){
            $query
                ->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$params['name'].'%');
        }

        if(isset($params['fee']) && !empty($params['fee'])){
            $query
                ->andWhere('l.fee = :fee')
                ->setParameter('fee', $params['fee']);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function findByParamsForReminders($params=array()){
        $query = $this->createQueryBuilder('l')->select("l");

        if(isset($params['expiry']) && !empty($params['expiry'])){
            $query->andWhere('DATEDIFF(l.endDate, NOW()) = :expiry')
                ->setParameter('expiry', $params['expiry']);
        }

        if(isset($params['renewal']) && !empty($params['renewal'])){
            $query
                ->andWhere('l.renewalStatus = :true')
                ->setParameter('true', true)
                ->andWhere("DATEDIFF(DATESUB(l.endDate, l.renewal, 'DAY'), NOW()) = :renewal")
                ->setParameter('renewal', $params['renewal'])
            ;
        }

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
