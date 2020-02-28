<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LandlordRepository")
 * @UniqueEntity(
 *     "name",
 *     message="Landlord already used"
 * )
 */
class Landlord
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Landlord type is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LandlordType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $type;

    /**
     * @Assert\NotBlank(message="Landlord number is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $number;

    /**
     * @Assert\NotBlank(message="Landlord name is required")
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Landlord address1 is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $address1;

    /**
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $address2;

    /**
     * @Assert\NotBlank(message="Landlord city is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    /**
     * @Assert\NotBlank(message="Landlord state is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $state;

    /**
     * @Assert\NotBlank(message="Landlord Postal Code is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $postalCode;

    /**
     * @Assert\NotBlank(message="Landlord Country is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $country;

    /**
     * @Assert\NotBlank(message="Account Holder is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $accountHolder;

    /**
     * @Assert\NotBlank(message="Bank Name is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $bankName;

    /**
     * @Assert\NotBlank(message="Branch Name is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $branchName;

    /**
     * @Assert\NotBlank(message="Branch Code is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $branchCode;

    /**
     * @Assert\NotBlank(message="Account Number is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $accountNumber;

    /**
     * @Assert\NotBlank(message="Account Type is required")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $accountType;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $bankDocument;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="VAR Register is required"
     * )
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $vatStatus;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $vatNumber;

    /**
     * @Assert\NotBlank(message="BEE Status is required")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BEEStatusType", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $beeStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $beeExpiry;

    /**
     * @Assert\NotBlank(message="Document Status is required")
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
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * Landlord constructor.
     * @param array $params
     * @throws \Exception
     */
    public function __construct($params = array())
    {
        $this->vatStatus = false;

        $this->documentStatusUpdated = new \DateTime();
        $this->created = new \DateTime();
        $this->update($params);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function update($params = array())
    {
        if(is_array($params)){
            foreach($params as $key => $value) {
                if (property_exists($this, $key) && $key != 'id' && $key != 'bankDocument' && $key != 'documentStatusUpdated') {
                    if ($key == 'beeExpiry'){
                        if(!empty($value)){
                            $this->$key = new \DateTime($value);
                        }
                        else{
                            $this->$key = null;
                        }
                    }
                    elseif ($key == 'vatStatus'){
                        if(!empty($value)){
                            $this->$key = true;
                        }
                        else{
                            $this->$key = false;
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
    public function getName(): ?string
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
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param mixed $address1
     */
    public function setAddress1($address1): void
    {
        $this->address1 = $address1;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address2
     */
    public function setAddress2($address2): void
    {
        $this->address2 = $address2;
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
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param mixed $accountHolder
     */
    public function setAccountHolder($accountHolder): void
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return mixed
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param mixed $bankName
     */
    public function setBankName($bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return mixed
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * @param mixed $branchName
     */
    public function setBranchName($branchName): void
    {
        $this->branchName = $branchName;
    }

    /**
     * @return mixed
     */
    public function getBranchCode()
    {
        return $this->branchCode;
    }

    /**
     * @param mixed $branchCode
     */
    public function setBranchCode($branchCode): void
    {
        $this->branchCode = $branchCode;
    }

    /**
     * @return mixed
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param mixed $accountNumber
     */
    public function setAccountNumber($accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param mixed $accountType
     */
    public function setAccountType($accountType): void
    {
        $this->accountType = $accountType;
    }

    /**
     * @return mixed
     */
    public function getBankDocument()
    {
        return $this->bankDocument;
    }

    /**
     * @param mixed $bankDocument
     */
    public function setBankDocument($bankDocument): void
    {
        $this->bankDocument = $bankDocument;
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
    public function getVatStatus()
    {
        return $this->vatStatus;
    }

    /**
     * @param mixed $vatStatus
     */
    public function setVatStatus($vatStatus): void
    {
        $this->vatStatus = $vatStatus;
    }

    /**
     * @return mixed
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * @param mixed $vatNumber
     */
    public function setVatNumber($vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    /**
     * @return mixed
     */
    public function getBeeStatus()
    {
        return $this->beeStatus;
    }

    /**
     * @param mixed $beeStatus
     */
    public function setBeeStatus($beeStatus): void
    {
        $this->beeStatus = $beeStatus;
    }

    /**
     * @return mixed
     */
    public function getBeeExpiry()
    {
        return $this->beeExpiry;
    }

    /**
     * @param mixed $beeExpiry
     */
    public function setBeeExpiry($beeExpiry): void
    {
        $this->beeExpiry = $beeExpiry;
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
