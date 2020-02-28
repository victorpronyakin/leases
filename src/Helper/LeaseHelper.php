<?php


namespace App\Helper;


use App\Entity\CPIRate;
use App\Entity\Lease;
use App\Entity\LeaseDocument;
use App\Entity\LeaseRentalCost;
use Doctrine\ORM\EntityManager;

class LeaseHelper
{
    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param null $date
     * @return int|mixed
     * @throws \Exception
     */
    public static function getTotalMonthlyCost(EntityManager $em, Lease $lease, $date=null){
        $totalCost = 0;
        $rentalCosts = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$lease]);
        if(!empty($rentalCosts)){
            if(!$date instanceof \DateTime){
                $date = new \DateTime();
            }
            foreach ($rentalCosts as $rentalCost){
                if($rentalCost instanceof LeaseRentalCost && $rentalCost->getStartDate()->format('Y-m-d') <= $date->format('Y-m-d')){
                    $totalCost += $rentalCost->getAmount();
                }
            }
        }

        return $totalCost;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return int|mixed
     * @throws \Exception
     */
    public static function getPreviousTotalMonthlyCost(EntityManager $em, Lease $lease){
        $previousLease = $em->getRepository(Lease::class)->findOnePreviousByLease($lease);
        if($previousLease instanceof Lease){
            return self::getTotalMonthlyCost($em, $previousLease, $previousLease->getEndDate());

        }

        return 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param null $date
     * @return float|int|mixed
     * @throws \Exception
     */
    public static function getCurrentTotalMonthlyCost(EntityManager $em, Lease $lease, $date = null){
        $totalCost = 0;
        $rentalCosts = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$lease]);
        if(!empty($rentalCosts)){
            if(!$date instanceof \DateTime){
                $date = new \DateTime();
            }
            $percentage = self::getLeaseRentalPercentage($em, $lease, $date);
            foreach ($rentalCosts as $rentalCost){
                if($rentalCost instanceof LeaseRentalCost && $rentalCost->getStartDate()->format('Y-m-d') <= $date->format('Y-m-d')){
                    $totalCost += self::getRentalCostAmountWithEscalation($lease, $rentalCost, $percentage, $date);
                }
            }
        }

        return $totalCost;
    }

    /**
     * @param Lease $lease
     * @param LeaseRentalCost $rentalCost
     * @param $percentage
     * @param null $date
     * @return float|mixed
     * @throws \Exception
     */
    public static function getRentalCostAmountWithEscalation(Lease $lease, LeaseRentalCost $rentalCost, $percentage, $date=null){
        if(!$date instanceof \DateTime){
            $date = new \DateTime();
        }
        $numberEscalation = self::getNumberEscalation($lease, $date);
        $rentalCostAmount = $rentalCost->getAmount();
        for($i=0; $i<$numberEscalation; $i++){
            $rentalCostAmount = $rentalCostAmount + round((($rentalCostAmount * $percentage) / 100), 2);
        }

        return $rentalCostAmount;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return int|mixed
     * @throws \Exception
     */
    public static function getPreviousCurrentTotalMonthlyCost(EntityManager $em, Lease $lease){
        $previousLease = $em->getRepository(Lease::class)->findOnePreviousByLease($lease);
        if($previousLease instanceof Lease){
            return self::getCurrentTotalMonthlyCost($em, $previousLease, $previousLease->getEndDate());

        }

        return 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param null $date
     * @return float|int|mixed
     * @throws \Exception
     */
    public static function getLeaseRentalPercentage(EntityManager $em, Lease $lease, $date = null){
        $percentage = 0;
        if(!$date instanceof \DateTime){
            $date = new \DateTime();
        }

        if($lease->getAnnualEscalationType() == 'cpi'){
            $cpiRate = $em->getRepository(CPIRate::class)->findOneBy(['month'=>$date->format('F Y')]);
            if($cpiRate instanceof CPIRate){
                $percentage = $cpiRate->getValue();
            }
            $percentage += $lease->getAnnualEscalationCpi();
        }
        else{
            $percentage = $lease->getAnnualEscalation();
        }

        return $percentage;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return float|int|mixed
     * @throws \Exception
     */
    public static function getPreviousLeaseRentalPercentage(EntityManager $em, Lease $lease){
        $previousLease = $em->getRepository(Lease::class)->findOnePreviousByLease($lease);
        if($previousLease instanceof Lease){
            return self::getLeaseRentalPercentage($em, $previousLease, $previousLease->getEndDate());
        }

        return 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return float|int
     * @throws \Exception
     */
    public static function getProposedLeaseByLandlord(EntityManager $em, Lease $lease){
        if(!empty($lease->getProposedLease())){
            return $lease->getProposedLease();
        }

        $currentRentalCost = self::getPreviousCurrentTotalMonthlyCost($em, $lease);
        $percentage = self::getPreviousLeaseRentalPercentage($em, $lease);

        return round(($currentRentalCost+(($currentRentalCost * $percentage) / 100)), 2);
    }

    /**
     * @param EntityManager $em
     * @param Lease $previousLease
     * @return float
     * @throws \Exception
     */
    public static function getProposedLeaseByPrevious(EntityManager $em, Lease $previousLease){
        $currentRentalCost = self::getCurrentTotalMonthlyCost($em, $previousLease);
        $percentage = self::getLeaseRentalPercentage($em, $previousLease);

        return round(($currentRentalCost+(($currentRentalCost * $percentage) / 100)), 2);
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param null $date
     * @return float|int
     * @throws \Exception
     */
    public static function getAgentSaving(EntityManager $em, Lease $lease, $date = null){
        $proposedRental = self::getProposedLeaseByLandlord($em, $lease);
        if($proposedRental != 0){
            $currentTotalCost = self::getTotalMonthlyCost($em, $lease, $date);

            return round($proposedRental - $currentTotalCost, 2);
        }

        return 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param null $date
     * @return float|int
     * @throws \Exception
     */
    public static function getAgentSavingPercentage(EntityManager $em, Lease $lease, $date = null){
        $proposedRental = self::getProposedLeaseByLandlord($em, $lease);
        if($proposedRental != 0){
            $currentTotalCost = self::getTotalMonthlyCost($em, $lease, $date);

            return round((($proposedRental - $currentTotalCost)/$proposedRental)*100, 2);
        }

        return 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param null $date
     * @return float|mixed
     * @throws \Exception
     */
    public static function getAgentBilling(EntityManager $em, Lease $lease, $date = null){
        if($lease->getFee() == 1){
            $currentTotalMonthly = self::getCurrentTotalMonthlyCost($em, $lease, $date);
            if(!empty($lease->getFeeValue())){
                $billing = round(($currentTotalMonthly*$lease->getFeeValue())/100,2);
            }
            else{
                $billing = 0;
            }
        }
        elseif ($lease->getFee() == 2){
            $agentSaving = self::getAgentSaving($em, $lease, $date);
            $percentage = self::getLeaseRentalPercentage($em, $lease, $date);
            $numberEscalation = self::getNumberEscalation($lease, $date);
            $billing = round(($agentSaving*$lease->getFeeValue())/100, 2);
            for($i=0; $i<$numberEscalation; $i++){
                $billing = $billing + round((($billing * $percentage) / 100), 2);
            }
        }
        else{
            $billing = $lease->getFeeValue();
        }

        return ($billing > 0) ? $billing : 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return float|int|mixed
     * @throws \Exception
     */
    public static function getEscalationSaving(EntityManager $em, Lease $lease){
        $percentageOld = self::getPreviousLeaseRentalPercentage($em, $lease);
        if($percentageOld != 0){
            $percentage = LeaseHelper::getLeaseRentalPercentage($em, $lease);
            return $percentageOld - $percentage;
        }

        return 0;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return float
     * @throws \Exception
     */
    public static function getTotalProposedLeaseContractValue(EntityManager $em, Lease $lease){
        $month = $lease->getTerm();
        $year = ceil($month/12);
        $proposedLease = self::getProposedLeaseByLandlord($em, $lease);
        $percentage = self::getPreviousLeaseRentalPercentage($em, $lease);
        $value = $proposedLease*12;
        for($i=1; $i<$year; $i++){
            $proposedLease = $proposedLease+($proposedLease*$percentage)/100;
            $value += $proposedLease*12;
        }

        return round($value, 2);
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return float
     * @throws \Exception
     */
    public static function getTotalNewLeaseContractValue(EntityManager $em, Lease $lease){
        $month = $lease->getTerm();
        $year = ceil($month/12);
        $newLeaseCost = self::getTotalMonthlyCost($em, $lease);
        $percentage = self::getLeaseRentalPercentage($em, $lease);
        $value = $newLeaseCost*12;
        for($i=1; $i<$year; $i++){
            $newLeaseCost = $newLeaseCost+($newLeaseCost*$percentage)/100;
            $value += $newLeaseCost*12;
        }

        return round($value, 2);
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return float
     * @throws \Exception
     */
    public static function getTotalContractValueSaving(EntityManager $em, Lease $lease){
        $proposedLeaseContractValue = self::getTotalProposedLeaseContractValue($em, $lease);
        $newLeaseContractValue = self::getTotalNewLeaseContractValue($em, $lease);

        return round($proposedLeaseContractValue - $newLeaseContractValue,2);
    }

    /**
     * @param Lease $lease
     * @param null $date
     * @return int
     * @throws \Exception
     */
    public static function getNumberEscalation(Lease $lease, $date=null){
        if(!$date instanceof \DateTime){
            $date = new \DateTime();
        }

        $checkData = new \DateTime($lease->getStartDate()->format('Y-m-d'));
        if($lease->getAnnualEscalationDate() == '12month'){
            $checkData->modify('+1 year');
        }
        else{
            $numberMonth = $lease->getAnnualEscalationDate();
            $year = $checkData->format('Y');
            $month = $checkData->format('m');
            if($month > $numberMonth){
                $year++;
            }
            $checkData->setDate($year, $numberMonth, 1);
        }

        if($date->format('Y-m') == $checkData->format('Y-m')){
            return 1;
        }
        elseif($date->format('Y-m') > $checkData->format('Y-m')){
            $diff = $date->diff($checkData);
            return 1 + $diff->y;
        }
        else{
            return 0;
        }
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @param LeaseRentalCost $rentalCost
     * @param null $date
     * @return float|mixed
     * @throws \Exception
     */
    public static function getRentalCostAmountWithEscalationWithoutPercentage(EntityManager $em, Lease $lease, LeaseRentalCost $rentalCost, $date=null){
        if(!$date instanceof \DateTime){
            $date = new \DateTime();
        }

        if($rentalCost instanceof LeaseRentalCost && $rentalCost->getStartDate()->format('Y-m-d') <= $date->format('Y-m-d')){
            $percentage = self::getLeaseRentalPercentage($em, $lease, $date);
            $numberEscalation = self::getNumberEscalation($lease, $date);
            $rentalCostAmount = $rentalCost->getAmount();
            for($i=0; $i<$numberEscalation; $i++){
                $rentalCostAmount = $rentalCostAmount + (($rentalCostAmount * $percentage) / 100);
            }
            return round($rentalCostAmount, 2);
        }

        return null;
    }

    /**
     * @param EntityManager $em
     * @param Lease $lease
     * @return array
     * @throws \Exception
     */
    public static function getPreviousLeaseWithRentalRange(EntityManager $em, Lease $lease){
        $previousLeases = [];
        $previousLeasesResult = $em->getRepository(Lease::class)->findPreviousByLease($lease);
        foreach ($previousLeasesResult as $previousLease){
            if($previousLease instanceof Lease){
                $previousLeaseDocuments = $em->getRepository(LeaseDocument::class)->findBy(['lease'=>$previousLease]);

                $commercials = [
                    'from' => [],
                    'to' => [],
                    'items' => [],
                    'totalMonthly' => [],
                    'totalAnnual' => [],
                ];
                $dateRangesFrom = [];
                $diffYears = $previousLease->getEndDate()->diff($previousLease->getStartDate())->y;
                for($i=0; $i<=$diffYears; $i++){
                    $fromDate = new \DateTime($previousLease->getStartDate()->format('Y-m-d'));
                    if($i>0){
                        $fromDate->modify('+'.$i.' years');
                    }
                    $dateRangesFrom[] = $fromDate;
                }
                $rentalCosts = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$previousLease]);
                if(!empty($rentalCosts)){
                    foreach ($rentalCosts as $rentalCost){
                        if(
                            $rentalCost instanceof LeaseRentalCost && $rentalCost->getAdditional() == true
                            && $rentalCost->getStartDate()->format('Y-m-d') > $previousLease->getStartDate()->format('Y-m-d')
                        ){
                            if(!in_array($rentalCost->getStartDate(), $dateRangesFrom)){
                                $dateRangesFrom[] = $rentalCost->getStartDate();
                            }
                        }
                    }
                }
                sort($dateRangesFrom);
                foreach ($dateRangesFrom as $key => $dateRangeFrom){
                    if($dateRangeFrom instanceof \DateTime){
                        array_push($commercials['from'], $dateRangeFrom);
                        if($key != 0){
                            $toDate = new \DateTime($dateRangeFrom->format('Y-m-d'));
                            $toDate->modify('-1 days');
                            array_push($commercials['to'], $toDate);
                        }
                    }
                }
                array_push($commercials['to'], $previousLease->getEndDate());

                if(!empty($rentalCosts)){
                    foreach ($rentalCosts as $rentalCost){
                        if($rentalCost instanceof LeaseRentalCost){
                            $amount = [];
                            $skip = 1;
                            foreach ($commercials['from'] as $dateFrom){
                                if($dateFrom instanceof \DateTime){
                                    $cost = self::getRentalCostAmountWithEscalationWithoutPercentage($em, $previousLease, $rentalCost, $dateFrom);
                                    $amount[] = $cost;
                                    if(is_null($cost)){
                                        $skip++;
                                    }
                                }
                            }

                            $commercials['items'][] = [
                                'name' => ($rentalCost->getAdditional()) ? "ADDENDUM - ".$rentalCost->getCategory()->getName() : $rentalCost->getCategory()->getName(),
                                'amount' => $amount,
                                'skip' => $skip
                            ];
                        }
                    }
                }

                $totalMonthly = [];
                $totalAnnual = [];
                foreach ($commercials['items'] as $item){
                    if(isset($item['amount']) && !empty($item['amount'])){
                        foreach ($item['amount'] as $key => $amountValue){
                            if(isset($totalMonthly[$key])){
                                $totalMonthly[$key] += $amountValue;
                                $totalAnnual[$key] += $amountValue*12;
                            }
                            else{
                                $totalMonthly[$key] = $amountValue;
                                $totalAnnual[$key] = $amountValue*12;
                            }
                        }
                    }

                }
                $commercials['totalMonthly'] = $totalMonthly;
                $commercials['totalAnnual'] = $totalAnnual;

                $previousLeases[] = [
                    'id' => $previousLease->getId(),
                    'startDate' => $previousLease->getStartDate(),
                    'endDate' => $previousLease->getEndDate(),
                    'renewalStatus' => $previousLease->getRenewalStatus(),
                    'renewal' => $previousLease->getRenewal(),
                    'annualEscalationType' => $previousLease->getAnnualEscalationType(),
                    'annualEscalation' => $previousLease->getAnnualEscalation(),
                    'annualEscalationCpi' => $previousLease->getAnnualEscalationCpi(),
                    'electricityType' => $previousLease->getElectricityType(),
                    'electricityFixed' => $previousLease->getElectricityFixed(),
                    'otherUtilityCost' => $previousLease->getOtherUtilityCost(),
                    'documentStatus' => $previousLease->getDocumentStatus(),
                    'documentStatusUpdated' => $previousLease->getDocumentStatusUpdated(),
                    'leaseDocuments' => $previousLeaseDocuments,
                    'commercials' => $commercials
                ];
            }
        }

        return $previousLeases;
    }
}
