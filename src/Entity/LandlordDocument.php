<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\LandlordDocumentRepository")
 */
class LandlordDocument
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="This field is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Landlord", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $landlord;

    /**
     * @Assert\NotBlank(message="This field is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LandlordDocumentType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $type;

    /**
     * @Assert\NotBlank(message="This field is required")
     *
     * @ORM\Column(type="string")
     */
    private $document;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * LandlordDocument constructor.
     * @param Landlord $landlord
     * @param LandlordDocumentType $type
     * @param $document
     * @param $name
     * @throws \Exception
     */
    public function __construct(Landlord $landlord, LandlordDocumentType $type, $document, $name)
    {
        $this->landlord = $landlord;
        $this->type = $type;
        $this->document = $document;
        $this->name = $name;
        $this->created = new \DateTime();
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
    public function getLandlord(): ?Landlord
    {
        return $this->landlord;
    }

    /**
     * @param Landlord $landlord
     */
    public function setLandlord(Landlord $landlord): void
    {
        $this->landlord = $landlord;
    }

    /**
     * @return mixed
     */
    public function getType(): ?LandlordDocumentType
    {
        return $this->type;
    }

    /**
     * @param LandlordDocumentType $type
     */
    public function setType(LandlordDocumentType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getDocument(): ?string
    {
        return $this->document;
    }

    /**
     * @param mixed $document
     */
    public function setDocument($document): void
    {
        $this->document = $document;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
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
