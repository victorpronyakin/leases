<?php


namespace App\Helper;


use App\Entity\Financial;
use App\Entity\Lease;
use App\Entity\LeaseElectricityType;
use App\Entity\LeaseRentalCost;
use Doctrine\ORM\EntityManager;

class FinancialHelper
{
    /**
     * @param EntityManager $em
     * @param $month
     * @param $queryData
     * @return array
     * @throws \Exception
     */
    public static function generateFinancialData(EntityManager $em, $month, $queryData){
        $finances = [];
        if(!empty($month)){
            $leases = $em->getRepository(Lease::class)->findAllForFinancialWithParams($queryData, $month);
            foreach ($leases as $lease){
                if($lease instanceof Lease){
                    $finances[] = self::generateLeaseFinancialData($em, $lease, $month);
                }
            }
        }

        return $finances;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param $month
     * @return array
     * @throws \Exception
     */
    public static function generateLeaseFinancialData(EntityManager $em, Lease $lease, $month){
        $financial = $em->getRepository(Financial::class)->findOneBy(['lease'=>$lease, 'month'=>$month]);
        $leaseRentalCost = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$lease]);

        $electricityCost = 0;
        $total = 0;
        if($financial instanceof Financial){
            $leaseExpense = $financial->getLeaseExpense();
            $electricityCost = $financial->getElectricityCost();
            if(!empty($financial->getTotal())){
                $total = $financial->getTotal();
            }
            else{
                $total += $financial->getLeaseCharge();
                $total += $financial->getOtherCost();
                $total += $electricityCost;
            }
        }
        else{
            $date = new \DateTime($month);
            if($lease->getFrequencyOfLeasePayments() == 'annually'){
                if($lease->getStartDate()->format('m') == $date->format('m')){
                    $leaseExpense = LeaseHelper::getTotalMonthlyCost($em, $lease, $date);
                    $leaseExpense *= 12;
                }
                else{
                    $leaseExpense = 0;
                }
            }
            else{
                $leaseExpense = LeaseHelper::getTotalMonthlyCost($em, $lease, $date);
            }

            if ($lease->getElectricityType() instanceof LeaseElectricityType && $lease->getElectricityType()->getId() == 3){
                $electricityCost = $lease->getElectricityFixed();
            }
            $total = $electricityCost;
        }


        return [
            'lease' => $lease,
            'leaseRentalCost' => $leaseRentalCost,
            'leaseExpense' => $leaseExpense,
            'leaseCharge' => ($financial instanceof Financial) ? $financial->getLeaseCharge() : 0,
            'leasePaymentStatus' => ($financial instanceof Financial) ? $financial->getLeasePaymentStatus() : null,
            'electricityCost' => $electricityCost,
            'electricityPaymentStatus' => ($financial instanceof Financial) ? $financial->getElectricityPaymentStatus() : null,
            'otherCost' => ($financial instanceof Financial) ? $financial->getOtherCost() : 0,
            'otherCostWater' => ($financial instanceof Financial) ? $financial->getOtherCostWater() : 0,
            'otherCostSewer' => ($financial instanceof Financial) ? $financial->getOtherCostSewer() : 0,
            'otherCostRates' => ($financial instanceof Financial) ? $financial->getOtherCostRates() : 0,
            'otherCostRefuse' => ($financial instanceof Financial) ? $financial->getOtherCostRefuse() : 0,
            'otherCostPaymentStatus' => ($financial instanceof Financial) ? $financial->getOtherCostPaymentStatus() : null,
            'total' => $total,
            'invoices' => ($financial instanceof Financial) ? $financial->getInvoices() : null,
        ];
    }
}
