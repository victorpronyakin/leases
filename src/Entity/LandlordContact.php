<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\LandlordContactRepository")
 */
class LandlordContact
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Landlord", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $landlord;

    /**
     * @Assert\NotBlank(message="Contact Type is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LandlordContactType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $type;

    /**
     * @Assert\NotBlank(message="Contact Name is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstName;

    /**
     * @Assert\NotBlank(message="Contact Surname is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastName;

    /**
     * @Assert\NotBlank(message="Contact Company is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $company;

    /**
     * @Assert\NotBlank(message="Contact Email is required")
     * @Assert\Email(message="Contact Email is invalid")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @Assert\NotBlank(message="Contact Mobile is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $mobile;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $landline;

    /**
     * LandlordContact constructor.
     * @param $landlord
     * @param LandlordContactType $landlordContactType
     * @param array $params
     */
    public function __construct($landlord, LandlordContactType $landlordContactType, $params = array())
    {
        $this->landlord = $landlord;
        $this->type = $landlordContactType;
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id' && $key != 'landlord' && $key != 'type') {
                $this->$key = $value;
            }

        }
    }

    /**
     * @param LandlordContactType $landlordContactType
     * @param array $params
     */
    public function update(LandlordContactType $landlordContactType, $params = array()){
        $this->type = $landlordContactType;
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id' && $key != 'landlord' && $key != 'type') {
                $this->$key = $value;
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company): void
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getLandline()
    {
        return $this->landline;
    }

    /**
     * @param mixed $landline
     */
    public function setLandline($landline): void
    {
        $this->landline = $landline;
    }
}
