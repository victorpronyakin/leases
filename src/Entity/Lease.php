<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\LeaseRepository")
 */
class Lease
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Landlord is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Landlord", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $landlord;

    /**
     * @Assert\NotBlank(message="Site is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $site;

    /**
     * @Assert\NotBlank(message="Lease Type is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LeaseType", cascade={"persist"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $type;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $sqm;

    /**
     * @Assert\NotBlank(message="Lease Term is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $term;

    /**
     * @Assert\NotBlank(message="Start Date is required")
     * @Assert\DateTime(message="Start Date should be date")
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @Assert\NotBlank(message="End Date is required")
     * @Assert\DateTime(message="Start Date should be date")
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="Renewal Option in lease is required"
     * )
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $renewalStatus;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $renewal;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="Termination Clause in lease is required"
     * )
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $terminationClauseStatus;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $terminationClause;

    /**
     * @Assert\NotBlank(message="Electricity is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LeaseElectricityType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $electricityType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $electricityFixed;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LeaseOtherUtilityCostCategory", cascade={"persist"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $otherUtilityCost;

    /**
     * @Assert\NotBlank(message="Frequency of lease payments is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $frequencyOfLeasePayments;

    /**
     * @Assert\NotBlank(message="Annual Escalation Type is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $annualEscalationType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $annualEscalation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $annualEscalationCpi;

    /**
     * @Assert\NotBlank(message="Annual Escalation Starting Date is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $annualEscalationDate;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="Deposit Payable is required"
     * )
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $depositStatus;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LeaseDepositType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $depositType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $depositAmount;

    /**
     * @Assert\NotBlank(message="Status of lease documentation is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LandlordDocumentStatus", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $documentStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $documentStatusUpdated;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="Agent To Manage is required"
     * )
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $agentStatus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $terminateStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $terminateDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $renewed;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $allocated;

    /**
     * 1 - % of Monthly Lease
     * 2 - % of Saving
     * 3 - Fixed Monthly Fee
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fee;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $feeValue;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $feeDuration;

    /**
     *  1 = NO ESCALATION
     *  2 = AS PER NEW LEASE TERMS
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $feeEscalation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $proposedLease;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $proposedLeaseManually;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $targetRenewalRental;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $targetRenewalEscalation;

    /**
     * Lease constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->type = new ArrayCollection();
        $this->otherUtilityCost = new ArrayCollection();
        $this->renewalStatus = false;
        $this->terminationClauseStatus = false;
        $this->depositStatus = false;
        $this->agentStatus = false;
        $this->terminateStatus = false;
        $this->renewed = false;
        $this->proposedLeaseManually = false;

        $this->documentStatusUpdated = new \DateTime();
        $this->created = new \DateTime();
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function update($params = array()){
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id' && $key != 'documentStatusUpdated') {
                if ($key == 'startDate' || $key == 'endDate' || $key == 'terminateDate'){
                    if(!empty($value)){
                        $this->$key = new \DateTime($value);
                    }
                    else{
                        $this->$key = null;
                    }
                }
                elseif ($key == 'renewalStatus' || $key == 'terminationClauseStatus' || $key == 'depositStatus' || $key == 'agentStatus' || $key == 'terminateStatus' || $key == 'proposedLeaseManually'){
                    if(!empty($value)){
                        $this->$key = true;
                    }
                    else{
                        $this->$key = false;
                    }
                }
                elseif ($key == 'type' || $key == 'otherUtilityCost'){
                    if(!empty($value)){
                        $this->$key = $value;
                    }
                    else{
                        $this->$key = new ArrayCollection();
                    }
                }
                else{
                    if(!empty($value)){
                        $this->$key = $value;
                    }
                    else{
                        $this->$key = null;
                    }
                }
            }
        }
        if (isset($params['fee']) && !empty($params['fee'])){
            if($params['fee'] == 1 || $params['fee'] == 2){
                $this->feeValue = (isset($params['feeValue1'])) ? $params['feeValue1'] : null;
            }
            else{
                $this->feeValue = (isset($params['feeValue2'])) ? $params['feeValue2'] : null;
            }
        }

        $this->updated = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLandlord()
    {
        return $this->landlord;
    }

    /**
     * @param mixed $landlord
     */
    public function setLandlord($landlord): void
    {
        $this->landlord = $landlord;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site): void
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @param LeaseType $type
     */
    public function addType(LeaseType $type)
    {
        $this->type->add($type);
    }

    /**
     * @param LeaseType $type
     */
    public function removeType(LeaseType $type)
    {
        $this->type->removeElement($type);
    }

    /**
     * @return mixed
     */
    public function getSqm()
    {
        return $this->sqm;
    }

    /**
     * @param mixed $sqm
     */
    public function setSqm($sqm): void
    {
        $this->sqm = $sqm;
    }

    /**
     * @return mixed
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param mixed $term
     */
    public function setTerm($term): void
    {
        $this->term = $term;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getRenewalStatus()
    {
        return $this->renewalStatus;
    }

    /**
     * @param mixed $renewalStatus
     */
    public function setRenewalStatus($renewalStatus): void
    {
        $this->renewalStatus = $renewalStatus;
    }

    /**
     * @return mixed
     */
    public function getRenewal()
    {
        return $this->renewal;
    }

    /**
     * @param mixed $renewal
     */
    public function setRenewal($renewal): void
    {
        $this->renewal = $renewal;
    }

    /**
     * @return mixed
     */
    public function getTerminationClauseStatus()
    {
        return $this->terminationClauseStatus;
    }

    /**
     * @param mixed $terminationClauseStatus
     */
    public function setTerminationClauseStatus($terminationClauseStatus): void
    {
        $this->terminationClauseStatus = $terminationClauseStatus;
    }

    /**
     * @return mixed
     */
    public function getTerminationClause()
    {
        return $this->terminationClause;
    }

    /**
     * @param mixed $terminationClause
     */
    public function setTerminationClause($terminationClause): void
    {
        $this->terminationClause = $terminationClause;
    }

    /**
     * @return mixed
     */
    public function getElectricityType()
    {
        return $this->electricityType;
    }

    /**
     * @param mixed $electricityType
     */
    public function setElectricityType($electricityType): void
    {
        $this->electricityType = $electricityType;
    }

    /**
     * @return mixed
     */
    public function getElectricityFixed()
    {
        return $this->electricityFixed;
    }

    /**
     * @param mixed $electricityFixed
     */
    public function setElectricityFixed($electricityFixed): void
    {
        $this->electricityFixed = $electricityFixed;
    }

    /**
     * @return mixed
     */
    public function getOtherUtilityCost()
    {
        return $this->otherUtilityCost;
    }

    /**
     * @param mixed $otherUtilityCost
     */
    public function setOtherUtilityCost($otherUtilityCost): void
    {
        $this->otherUtilityCost = $otherUtilityCost;
    }

    /**
     * @param LeaseOtherUtilityCostCategory $otherUtilityCostCategory
     */
    public function addOtherUtilityCost(LeaseOtherUtilityCostCategory $otherUtilityCostCategory)
    {
        $this->otherUtilityCost->add($otherUtilityCostCategory);
    }

    /**
     * @param LeaseOtherUtilityCostCategory $otherUtilityCostCategor
     */
    public function removeOtherUtilityCost(LeaseOtherUtilityCostCategory $otherUtilityCostCategor)
    {
        $this->otherUtilityCost->removeElement($otherUtilityCostCategor);
    }

    /**
     * @return mixed
     */
    public function getFrequencyOfLeasePayments()
    {
        return $this->frequencyOfLeasePayments;
    }

    /**
     * @param mixed $frequencyOfLeasePayments
     */
    public function setFrequencyOfLeasePayments($frequencyOfLeasePayments): void
    {
        $this->frequencyOfLeasePayments = $frequencyOfLeasePayments;
    }

    /**
     * @return mixed
     */
    public function getAnnualEscalationType()
    {
        return $this->annualEscalationType;
    }

    /**
     * @param mixed $annualEscalationType
     */
    public function setAnnualEscalationType($annualEscalationType): void
    {
        $this->annualEscalationType = $annualEscalationType;
    }

    /**
     * @return mixed
     */
    public function getAnnualEscalation()
    {
        return $this->annualEscalation;
    }

    /**
     * @param mixed $annualEscalation
     */
    public function setAnnualEscalation($annualEscalation): void
    {
        $this->annualEscalation = $annualEscalation;
    }

    /**
     * @return mixed
     */
    public function getAnnualEscalationCpi()
    {
        return $this->annualEscalationCpi;
    }

    /**
     * @param mixed $annualEscalationCpi
     */
    public function setAnnualEscalationCpi($annualEscalationCpi): void
    {
        $this->annualEscalationCpi = $annualEscalationCpi;
    }

    /**
     * @return mixed
     */
    public function getAnnualEscalationDate()
    {
        return $this->annualEscalationDate;
    }

    /**
     * @param mixed $annualEscalationDate
     */
    public function setAnnualEscalationDate($annualEscalationDate): void
    {
        $this->annualEscalationDate = $annualEscalationDate;
    }

    /**
     * @return mixed
     */
    public function getDepositStatus()
    {
        return $this->depositStatus;
    }

    /**
     * @param mixed $depositStatus
     */
    public function setDepositStatus($depositStatus): void
    {
        $this->depositStatus = $depositStatus;
    }

    /**
     * @return mixed
     */
    public function getDepositType()
    {
        return $this->depositType;
    }

    /**
     * @param mixed $depositType
     */
    public function setDepositType($depositType): void
    {
        $this->depositType = $depositType;
    }

    /**
     * @return mixed
     */
    public function getDepositAmount()
    {
        return $this->depositAmount;
    }

    /**
     * @param mixed $depositAmount
     */
    public function setDepositAmount($depositAmount): void
    {
        $this->depositAmount = $depositAmount;
    }

    /**
     * @return mixed
     */
    public function getDocumentStatus()
    {
        return $this->documentStatus;
    }

    /**
     * @param mixed $documentStatus
     */
    public function setDocumentStatus($documentStatus): void
    {
        $this->documentStatus = $documentStatus;
    }

    /**
     * @return mixed
     */
    public function getDocumentStatusUpdated()
    {
        return $this->documentStatusUpdated;
    }

    /**
     * @param mixed $documentStatusUpdated
     */
    public function setDocumentStatusUpdated($documentStatusUpdated): void
    {
        $this->documentStatusUpdated = $documentStatusUpdated;
    }

    /**
     * @return mixed
     */
    public function getAgentStatus()
    {
        return $this->agentStatus;
    }

    /**
     * @param mixed $agentStatus
     */
    public function setAgentStatus($agentStatus): void
    {
        $this->agentStatus = $agentStatus;
    }

    /**
     * @return mixed
     */
    public function getTerminateStatus()
    {
        return $this->terminateStatus;
    }

    /**
     * @param mixed $terminateStatus
     */
    public function setTerminateStatus($terminateStatus): void
    {
        $this->terminateStatus = $terminateStatus;
    }

    /**
     * @return mixed
     */
    public function getTerminateDate()
    {
        return $this->terminateDate;
    }

    /**
     * @param mixed $terminateDate
     */
    public function setTerminateDate($terminateDate): void
    {
        $this->terminateDate = $terminateDate;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated): void
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getRenewed()
    {
        return $this->renewed;
    }

    /**
     * @param mixed $renewed
     */
    public function setRenewed($renewed): void
    {
        $this->renewed = $renewed;
    }


    /**
     * @return mixed
     */
    public function getAllocated()
    {
        return $this->allocated;
    }

    /**
     * @param mixed $allocated
     */
    public function setAllocated($allocated): void
    {
        $this->allocated = $allocated;
    }

    /**
     * @return mixed
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param mixed $fee
     */
    public function setFee($fee): void
    {
        $this->fee = $fee;
    }

    /**
     * @return mixed
     */
    public function getFeeValue()
    {
        return $this->feeValue;
    }

    /**
     * @param mixed $feeValue
     */
    public function setFeeValue($feeValue): void
    {
        $this->feeValue = $feeValue;
    }

    /**
     * @return mixed
     */
    public function getFeeDuration()
    {
        return $this->feeDuration;
    }

    /**
     * @param mixed $feeDuration
     */
    public function setFeeDuration($feeDuration): void
    {
        $this->feeDuration = $feeDuration;
    }

    /**
     * @return mixed
     */
    public function getFeeEscalation()
    {
        return $this->feeEscalation;
    }

    /**
     * @param mixed $feeEscalation
     */
    public function setFeeEscalation($feeEscalation): void
    {
        $this->feeEscalation = $feeEscalation;
    }

    /**
     * @return mixed
     */
    public function getProposedLease()
    {
        return $this->proposedLease;
    }

    /**
     * @param mixed $proposedLease
     */
    public function setProposedLease($proposedLease): void
    {
        $this->proposedLease = $proposedLease;
    }

    /**
     * @return mixed
     */
    public function getProposedLeaseManually()
    {
        return $this->proposedLeaseManually;
    }

    /**
     * @param mixed $proposedLeaseManually
     */
    public function setProposedLeaseManually($proposedLeaseManually): void
    {
        $this->proposedLeaseManually = $proposedLeaseManually;
    }

    /**
     * @return mixed
     */
    public function getTargetRenewalRental()
    {
        return $this->targetRenewalRental;
    }

    /**
     * @param mixed $targetRenewalRental
     */
    public function setTargetRenewalRental($targetRenewalRental): void
    {
        $this->targetRenewalRental = $targetRenewalRental;
    }

    /**
     * @return mixed
     */
    public function getTargetRenewalEscalation()
    {
        return $this->targetRenewalEscalation;
    }

    /**
     * @param mixed $targetRenewalEscalation
     */
    public function setTargetRenewalEscalation($targetRenewalEscalation): void
    {
        $this->targetRenewalEscalation = $targetRenewalEscalation;
    }
}
