<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FinancialRepository")
 */
class Financial
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $month;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lease", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $lease;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $leaseExpense;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $leaseCharge;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FinancialStatus", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $leasePaymentStatus;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $leasePaymentStatusUpdated;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $electricityCost;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FinancialStatus", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $electricityPaymentStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $electricityPaymentStatusUpdated;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCost;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCostWater;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCostSewer;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCostRates;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $otherCostRefuse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FinancialStatus", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $otherCostPaymentStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $otherCostPaymentStatusUpdated;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $invoices;

    /**
     * Financial constructor.
     * @param $month
     * @param $lease
     */
    public function __construct($month, $lease)
    {
        $this->month = $month;
        $this->lease = $lease;
    }

    /**
     * @param array $params
     */
    public function update($params = array()){
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id' && $key != 'month' && $key != 'lease' ) {
                if(!empty($value)){
                    $this->$key = $value;
                }
                else{
                    $this->$key = null;
                }
            }
        }
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
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param mixed $month
     */
    public function setMonth($month): void
    {
        $this->month = $month;
    }

    /**
     * @return mixed
     */
    public function getLease()
    {
        return $this->lease;
    }

    /**
     * @param mixed $lease
     */
    public function setLease($lease): void
    {
        $this->lease = $lease;
    }

    /**
     * @return mixed
     */
    public function getLeaseExpense()
    {
        return $this->leaseExpense;
    }

    /**
     * @param mixed $leaseExpense
     */
    public function setLeaseExpense($leaseExpense): void
    {
        $this->leaseExpense = $leaseExpense;
    }

    /**
     * @return mixed
     */
    public function getLeaseCharge()
    {
        return $this->leaseCharge;
    }

    /**
     * @param mixed $leaseCharge
     */
    public function setLeaseCharge($leaseCharge): void
    {
        $this->leaseCharge = $leaseCharge;
    }

    /**
     * @return mixed
     */
    public function getLeasePaymentStatus()
    {
        return $this->leasePaymentStatus;
    }

    /**
     * @param mixed $leasePaymentStatus
     */
    public function setLeasePaymentStatus($leasePaymentStatus): void
    {
        $this->leasePaymentStatus = $leasePaymentStatus;
    }

    /**
     * @return mixed
     */
    public function getLeasePaymentStatusUpdated()
    {
        return $this->leasePaymentStatusUpdated;
    }

    /**
     * @param mixed $leasePaymentStatusUpdated
     */
    public function setLeasePaymentStatusUpdated($leasePaymentStatusUpdated): void
    {
        $this->leasePaymentStatusUpdated = $leasePaymentStatusUpdated;
    }

    /**
     * @return mixed
     */
    public function getElectricityCost()
    {
        return $this->electricityCost;
    }

    /**
     * @param mixed $electricityCost
     */
    public function setElectricityCost($electricityCost): void
    {
        $this->electricityCost = $electricityCost;
    }

    /**
     * @return mixed
     */
    public function getElectricityPaymentStatus()
    {
        return $this->electricityPaymentStatus;
    }

    /**
     * @param mixed $electricityPaymentStatus
     */
    public function setElectricityPaymentStatus($electricityPaymentStatus): void
    {
        $this->electricityPaymentStatus = $electricityPaymentStatus;
    }

    /**
     * @return mixed
     */
    public function getElectricityPaymentStatusUpdated()
    {
        return $this->electricityPaymentStatusUpdated;
    }

    /**
     * @param mixed $electricityPaymentStatusUpdated
     */
    public function setElectricityPaymentStatusUpdated($electricityPaymentStatusUpdated): void
    {
        $this->electricityPaymentStatusUpdated = $electricityPaymentStatusUpdated;
    }

    /**
     * @return mixed
     */
    public function getOtherCost()
    {
        return $this->otherCost;
    }

    /**
     * @param mixed $otherCost
     */
    public function setOtherCost($otherCost): void
    {
        $this->otherCost = $otherCost;
    }

    /**
     * @return mixed
     */
    public function getOtherCostWater()
    {
        return $this->otherCostWater;
    }

    /**
     * @param mixed $otherCostWater
     */
    public function setOtherCostWater($otherCostWater): void
    {
        $this->otherCostWater = $otherCostWater;
    }

    /**
     * @return mixed
     */
    public function getOtherCostSewer()
    {
        return $this->otherCostSewer;
    }

    /**
     * @param mixed $otherCostSewer
     */
    public function setOtherCostSewer($otherCostSewer): void
    {
        $this->otherCostSewer = $otherCostSewer;
    }

    /**
     * @return mixed
     */
    public function getOtherCostRates()
    {
        return $this->otherCostRates;
    }

    /**
     * @param mixed $otherCostRates
     */
    public function setOtherCostRates($otherCostRates): void
    {
        $this->otherCostRates = $otherCostRates;
    }

    /**
     * @return mixed
     */
    public function getOtherCostRefuse()
    {
        return $this->otherCostRefuse;
    }

    /**
     * @param mixed $otherCostRefuse
     */
    public function setOtherCostRefuse($otherCostRefuse): void
    {
        $this->otherCostRefuse = $otherCostRefuse;
    }

    /**
     * @return mixed
     */
    public function getOtherCostPaymentStatus()
    {
        return $this->otherCostPaymentStatus;
    }

    /**
     * @param mixed $otherCostPaymentStatus
     */
    public function setOtherCostPaymentStatus($otherCostPaymentStatus): void
    {
        $this->otherCostPaymentStatus = $otherCostPaymentStatus;
    }

    /**
     * @return mixed
     */
    public function getOtherCostPaymentStatusUpdated()
    {
        return $this->otherCostPaymentStatusUpdated;
    }

    /**
     * @param mixed $otherCostPaymentStatusUpdated
     */
    public function setOtherCostPaymentStatusUpdated($otherCostPaymentStatusUpdated): void
    {
        $this->otherCostPaymentStatusUpdated = $otherCostPaymentStatusUpdated;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @param mixed $invoices
     */
    public function setInvoices($invoices): void
    {
        $this->invoices = $invoices;
    }
}
