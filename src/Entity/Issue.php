<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 */
class Issue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Site is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $site;

    /**
     * @Assert\NotBlank(message="Issue Type is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\IssueType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $type;

    /**
     * @Assert\NotBlank(message="Issue Details is required")
     *
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="Issue Status is required"
     * )
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $logged;

    /**
     * Issue constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->status = true;
        $this->logged = new \DateTime();
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
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     */
    public function setDetails($details): void
    {
        $this->details = $details;
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
    public function getLogged()
    {
        return $this->logged;
    }

    /**
     * @param mixed $logged
     */
    public function setLogged($logged): void
    {
        $this->logged = $logged;
    }
}
