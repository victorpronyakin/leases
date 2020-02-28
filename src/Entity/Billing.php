<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BillingRepository")
 */
class Billing
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invoiced;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $paid;

    /**
     * Billing constructor.
     * @param $month
     * @param $lease
     * @param $invoiced
     * @param $paid
     */
    public function __construct($month, $lease, $invoiced=false, $paid=false)
    {
        $this->month = $month;
        $this->lease = $lease;
        $this->invoiced = $invoiced;
        $this->paid = $paid;
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
    public function getInvoiced()
    {
        return $this->invoiced;
    }

    /**
     * @param mixed $invoiced
     */
    public function setInvoiced($invoiced): void
    {
        $this->invoiced = $invoiced;
    }

    /**
     * @return mixed
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param mixed $paid
     */
    public function setPaid($paid): void
    {
        $this->paid = $paid;
    }
}
