<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 * @UniqueEntity(
 *     "number",
 *     message="Site ID already used"
 * )
 */
class Site
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Site ID is required")
     *
     * @ORM\Column(type="string")
     */
    private $number;

    /**
     * @Assert\NotBlank(message="Site Name is required")
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Site Address is required")
     *
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @Assert\NotBlank(message="Street Number is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $streetNumber;

    /**
     * @Assert\NotBlank(message="Street is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $street;

    /**
     * @Assert\NotBlank(message="City is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    /**
     * @Assert\NotBlank(message="State is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $state;

    /**
     * @Assert\NotBlank(message="Postal Code is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalCode;

    /**
     * @Assert\NotBlank(message="Country is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $country;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $erf;

    /**
     * @Assert\NotBlank(message="Hours of access is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\HoursOfAccess", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $hoursOfAccess;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $otherAccess;

    /**
     * @Assert\NotBlank(message="Primary Emergency Contact is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LandlordContact", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $primaryEmergencyContact;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\LandlordContact", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $secondaryEmergencyContact;

    /**
     * @Assert\NotBlank(message="Site Status is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ManagementStatus", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $siteStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $siteStatusUpdated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emergencyAccessUpdated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * Site constructor.
     * @param array $params
     * @throws \Exception
     */
    public function __construct($params = array())
    {
        $this->created = new \DateTime();
        $this->siteStatusUpdated = new \DateTime();
        $this->emergencyAccessUpdated = new \DateTime();
        $this->update($params);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function update($params = array()){
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id') {
                if ($key == 'emergencyAccessUpdated'){
                    if(!empty($value)){
                        $this->$key = new \DateTime($value);
                    }
                    else{
                        $this->$key = null;
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
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number): void
    {
        $this->number = $number;
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @param mixed $streetNumber
     */
    public function setStreetNumber($streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street): void
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getErf()
    {
        return $this->erf;
    }

    /**
     * @param mixed $erf
     */
    public function setErf($erf): void
    {
        $this->erf = $erf;
    }

    /**
     * @return mixed
     */
    public function getHoursOfAccess()
    {
        return $this->hoursOfAccess;
    }

    /**
     * @param mixed $hoursOfAccess
     */
    public function setHoursOfAccess($hoursOfAccess): void
    {
        $this->hoursOfAccess = $hoursOfAccess;
    }

    /**
     * @return mixed
     */
    public function getOtherAccess()
    {
        return $this->otherAccess;
    }

    /**
     * @param mixed $otherAccess
     */
    public function setOtherAccess($otherAccess): void
    {
        $this->otherAccess = $otherAccess;
    }

    /**
     * @return mixed
     */
    public function getPrimaryEmergencyContact()
    {
        return $this->primaryEmergencyContact;
    }

    /**
     * @param mixed $primaryEmergencyContact
     */
    public function setPrimaryEmergencyContact($primaryEmergencyContact): void
    {
        $this->primaryEmergencyContact = $primaryEmergencyContact;
    }

    /**
     * @return mixed
     */
    public function getSecondaryEmergencyContact()
    {
        return $this->secondaryEmergencyContact;
    }

    /**
     * @param mixed $secondaryEmergencyContact
     */
    public function setSecondaryEmergencyContact($secondaryEmergencyContact): void
    {
        $this->secondaryEmergencyContact = $secondaryEmergencyContact;
    }

    /**
     * @return mixed
     */
    public function getSiteStatus()
    {
        return $this->siteStatus;
    }

    /**
     * @param mixed $siteStatus
     */
    public function setSiteStatus($siteStatus): void
    {
        $this->siteStatus = $siteStatus;
    }

    /**
     * @return mixed
     */
    public function getSiteStatusUpdated()
    {
        return $this->siteStatusUpdated;
    }

    /**
     * @param mixed $siteStatusUpdated
     */
    public function setSiteStatusUpdated($siteStatusUpdated): void
    {
        $this->siteStatusUpdated = $siteStatusUpdated;
    }

    /**
     * @return mixed
     */
    public function getEmergencyAccessUpdated()
    {
        return $this->emergencyAccessUpdated;
    }

    /**
     * @param mixed $emergencyAccessUpdated
     */
    public function setEmergencyAccessUpdated($emergencyAccessUpdated): void
    {
        $this->emergencyAccessUpdated = $emergencyAccessUpdated;
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
}
