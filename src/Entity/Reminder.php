<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReminderRepository")
 */
class Reminder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Reminder Type is required")
     *
     * 1 = Lease Expiry
     * 2 = Lease Renewal Option
     * 3 = Lease Escalation
     * 4 = Issue
     * 5 = Missing Supporting Documents (LANDLORD)
     * 6 = Missing Supporting Documents (LEASE)
     * 7 = Site Status
     * 8 = Financial
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type;

    /**
     * @Assert\NotBlank(message="Reminder Details is required")
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $detail;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $dashboard;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $emailStatus;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $emails;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * Reminder constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created = new \DateTime();
    }

    /**
     * @param array $params
     */
    public function update($params=array()){
        $this->type = (isset($params['type'])) ? $params['type'] : null;
        $details = [];
        switch ($this->type){
            case 1:
                if(isset($params['detail']['leaseExpiry']) && !empty($params['detail']['leaseExpiry'])){
                    $details['leaseExpiry'] = $params['detail']['leaseExpiry'];
                }
                break;
            case 2:
                if(isset($params['detail']['leaseRenewal']) && !empty($params['detail']['leaseRenewal'])){
                    $details['leaseRenewal'] = $params['detail']['leaseRenewal'];
                }
                break;
            case 3:
                if(isset($params['detail']['leaseEscalation']) && !empty($params['detail']['leaseEscalation'])){
                    $details['leaseEscalation'] = $params['detail']['leaseEscalation'];
                }
                break;
            case 4:
                if(isset($params['detail']['issue1']) && !empty($params['detail']['issue1'])){
                    $details['issue'] = $params['detail']['issue1'];
                }
                else if(isset($params['detail']['issue']) && !empty($params['detail']['issue'])){
                    $details['issue'] = $params['detail']['issue'];
                }
                break;
            case 5:
                if(isset($params['detail']['landlordDocumentStatus']) && !empty($params['detail']['landlordDocumentStatus'])
                    && isset($params['detail']['landlordDocumentDay']) && !empty($params['detail']['landlordDocumentDay'])
                ){
                    $details['landlordDocumentStatus'] = $params['detail']['landlordDocumentStatus'];
                    $details['landlordDocumentDay'] = $params['detail']['landlordDocumentDay'];
                }
                break;
            case 6:
                if(isset($params['detail']['leaseDocumentStatus']) && !empty($params['detail']['leaseDocumentStatus'])
                    && isset($params['detail']['leaseDocumentDay']) && !empty($params['detail']['leaseDocumentDay'])
                ){
                    $details['leaseDocumentStatus'] = $params['detail']['leaseDocumentStatus'];
                    $details['leaseDocumentDay'] = $params['detail']['leaseDocumentDay'];
                }
                break;
            case 7:
                if(isset($params['detail']['siteStatus']) && !empty($params['detail']['siteStatus'])
                    && isset($params['detail']['siteDay']) && !empty($params['detail']['siteDay'])
                ){
                    $details['siteStatus'] = $params['detail']['siteStatus'];
                    $details['siteDay'] = $params['detail']['siteDay'];
                }
                break;
            case 8:
                if(isset($params['detail']['financialStatus']) && !empty($params['detail']['financialStatus'])
                    && isset($params['detail']['financialDay']) && !empty($params['detail']['financialDay'])
                ){
                    $details['financialStatus'] = $params['detail']['financialStatus'];
                    $details['financialDay'] = $params['detail']['financialDay'];
                }
                break;
        }
        $this->detail = json_encode($details);
        $dashboard = false;
        if(isset($params['dashboard'])){
            $dashboard = true;
        }
        $this->dashboard = $dashboard;
        $emailStatus = false;
        if(isset($params['emailStatus'])){
            $emailStatus = true;
        }
        $this->emailStatus =$emailStatus;
        if($this->emailStatus == true){
            $emails = [
                'users'=>[],
                'emails'=>[]
            ];
            if(isset($params['emails'])){
                if(isset($params['emails']['users']) && !empty($params['emails']['users'])){
                    $emails['users'] = $params['emails']['users'];
                }

                if(isset($params['emails']['emails']) && !empty($params['emails']['emails'])){
                    foreach ($params['emails']['emails'] as $email){
                        if(!empty($email)){
                            $emails['emails'][] = $email;
                        }
                    }
                }
            }
            $this->emails=json_encode($emails);
        }
        else{
            $this->emails = null;
        }
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
    public function getDetail()
    {
        return json_decode($this->detail, true);
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail): void
    {
        $this->detail = json_encode($detail);
    }

    /**
     * @return mixed
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * @param mixed $dashboard
     */
    public function setDashboard($dashboard): void
    {
        $this->dashboard = $dashboard;
    }

    /**
     * @return mixed
     */
    public function getEmailStatus()
    {
        return $this->emailStatus;
    }

    /**
     * @param mixed $emailStatus
     */
    public function setEmailStatus($emailStatus): void
    {
        $this->emailStatus = $emailStatus;
    }

    /**
     * @return mixed
     */
    public function getEmails()
    {
        return json_decode($this->emails, true);
    }

    /**
     * @param mixed $emails
     */
    public function setEmails($emails): void
    {
        $this->emails = json_encode($emails);
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
