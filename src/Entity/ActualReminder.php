<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActualReminderRepository")
 */
class ActualReminder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Reminder", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $reminder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lease", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $lease;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Landlord", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $landlord;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Issue", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $issue;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $snoozeDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * ActualReminder constructor.
     * @param Reminder $reminder
     * @param Site|null $site
     * @param Lease|null $lease
     * @param Landlord|null $landlord
     * @param Issue|null $issue
     * @throws \Exception
     */
    public function __construct(Reminder $reminder, Site $site = null, Lease $lease = null, Landlord $landlord = null, Issue $issue = null)
    {
        $this->reminder = $reminder;
        $this->site = $site;
        $this->lease = $lease;
        $this->landlord = $landlord;
        $this->issue = $issue;
        $this->status = $reminder->getDashboard();

        $this->created = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * @param mixed $reminder
     */
    public function setReminder($reminder): void
    {
        $this->reminder = $reminder;
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
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param mixed $issue
     */
    public function setIssue($issue): void
    {
        $this->issue = $issue;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSnoozeDate()
    {
        return $this->snoozeDate;
    }

    /**
     * @param mixed $snoozeDate
     */
    public function setSnoozeDate($snoozeDate): void
    {
        $this->snoozeDate = $snoozeDate;
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
}
