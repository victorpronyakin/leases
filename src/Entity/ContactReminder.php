<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactReminderRepository")
 */
class ContactReminder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * 1 = All Contact
     * 2 = Emergency Contact
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $month;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allCorrect;

    /**
     * @ORM\Column(type="boolean")
     */
    private $updateInfo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $noLonger;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $updateInfoEmail;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateLastSend;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateNextSend;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * ContactReminder constructor.
     * @param $type
     * @param $month
     * @param $text
     * @param bool $allCorrect
     * @param bool $updateInfo
     * @param bool $noLonger
     * @param string $updateInfoEmail
     * @param bool $status
     * @throws \Exception
     */
    public function __construct($type, $month, $text, $allCorrect=true, $updateInfo=true, $noLonger=true, $updateInfoEmail='', $status=true)
    {
        $this->type = $type;
        $this->month = $month;
        $this->text = $text;
        $this->allCorrect = $allCorrect;
        $this->updateInfo = $updateInfo;
        $this->noLonger = $noLonger;
        $this->updateInfoEmail = $updateInfoEmail;
        $this->status = $status;
        $this->dateLastSend = new \DateTime();
        $this->dateNextSend = new \DateTime('+'.$month.' months');
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getAllCorrect()
    {
        return $this->allCorrect;
    }

    /**
     * @param mixed $allCorrect
     */
    public function setAllCorrect($allCorrect): void
    {
        $this->allCorrect = $allCorrect;
    }

    /**
     * @return mixed
     */
    public function getUpdateInfo()
    {
        return $this->updateInfo;
    }

    /**
     * @param mixed $updateInfo
     */
    public function setUpdateInfo($updateInfo): void
    {
        $this->updateInfo = $updateInfo;
    }

    /**
     * @return mixed
     */
    public function getNoLonger()
    {
        return $this->noLonger;
    }

    /**
     * @param mixed $noLonger
     */
    public function setNoLonger($noLonger): void
    {
        $this->noLonger = $noLonger;
    }

    /**
     * @return mixed
     */
    public function getUpdateInfoEmail()
    {
        return $this->updateInfoEmail;
    }

    /**
     * @param mixed $updateInfoEmail
     */
    public function setUpdateInfoEmail($updateInfoEmail): void
    {
        $this->updateInfoEmail = $updateInfoEmail;
    }

    /**
     * @return mixed
     */
    public function getDateLastSend()
    {
        return $this->dateLastSend;
    }

    /**
     * @param mixed $dateLastSend
     */
    public function setDateLastSend($dateLastSend): void
    {
        $this->dateLastSend = $dateLastSend;
    }

    /**
     * @return mixed
     */
    public function getDateNextSend()
    {
        return $this->dateNextSend;
    }

    /**
     * @param mixed $dateNextSend
     */
    public function setDateNextSend($dateNextSend): void
    {
        $this->dateNextSend = $dateNextSend;
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
}
