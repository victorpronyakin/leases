<?php


namespace App\Helper;


use App\Entity\ActualReminder;
use App\Entity\FinancialStatus;
use App\Entity\Landlord;
use App\Entity\LandlordDocumentStatus;
use App\Entity\Lease;
use App\Entity\ManagementStatus;
use App\Entity\Reminder;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class ReminderHelper
 * @package App\Helper
 */
class ReminderHelper
{
    /**
     * @param EntityManager $em
     * @param $reminders
     * @return array
     */
    public static function generateRemindersDataForList(EntityManager $em, $reminders){
        $result = [];
        if(!empty($reminders)){
            foreach ($reminders as $reminder){
                if($reminder instanceof Reminder){

                    $result[] = self::generateReminderDataForList($em, $reminder);
                }
            }
        }

        return $result;
    }

    /**
     * @param EntityManager $em
     * @param Reminder $reminder
     * @return array
     */
    public static function generateReminderDataForList(EntityManager $em, Reminder $reminder){
        $delivery = '';
        if($reminder->getDashboard()){
            $delivery .= 'Dashboard';
        }
        if($reminder->getEmailStatus()){
            if(!empty($delivery)){
                $delivery .= ', Email to ';
            }
            else{
                $delivery = 'Email to ';
            }
            if(!empty($reminder->getEmails())){
                $emailsStr = '';
                $emails = $reminder->getEmails();
                if(isset($emails['users']) && !empty($emails['users'])){
                    foreach ($emails['users'] as $userID){
                        $user = $em->getRepository(User::class)->find($userID);
                        if($user instanceof User){
                            if(!empty($emailsStr)){
                                $emailsStr .= "AND ".$user->getFirstName()." ".$user->getLastName()." ";
                            }
                            else{
                                $emailsStr .= $user->getFirstName()." ".$user->getLastName()." ";
                            }
                        }
                    }
                }
                if(isset($emails['emails']) && !empty($emails['emails'])){
                    foreach ($emails['emails'] as $email){
                        if(!empty($email)){
                            if(!empty($emailsStr)){
                                $emailsStr .= "AND ".$email." ";
                            }
                            else{
                                $emailsStr .= $email." ";
                            }
                        }
                    }
                }

                $delivery .= $emailsStr;

            }
        }

        $detailsStr = '';
        $details = $reminder->getDetail();
        switch ($reminder->getType()){
            case 1:
                if(isset($details['leaseExpiry']) && !empty($details['leaseExpiry'])){
                    $detailsStr = $details['leaseExpiry']. ' days before expiry';
                }
                break;
            case 2:
                if(isset($details['leaseRenewal']) && !empty($details['leaseRenewal'])){
                    $detailsStr = $details['leaseRenewal']. ' days before lease option expires';
                }
                break;
            case 3:
                if(isset($details['leaseEscalation']) && !empty($details['leaseEscalation'])){
                    $detailsStr = $details['leaseEscalation']. ' days before annual lease escalation';
                }
                break;
            case 4:
                if(isset($details['issue']) && !empty($details['issue'])){
                    if($details['issue'] == 'immediately'){
                        $detailsStr = 'Immediately after issue is logged';
                    }
                    else{
                        $detailsStr = $details['issue']. ' days after issue is logged';
                    }
                }
                break;
            case 5:
                if(isset($details['landlordDocumentStatus']) && !empty($details['landlordDocumentStatus'])
                    && isset($details['landlordDocumentDay']) && !empty($details['landlordDocumentDay'])
                ){
                    $detailsStr = $details['landlordDocumentDay']. ' days after ';
                    $documentStatus = $em->getRepository(LandlordDocumentStatus::class)->find($details['landlordDocumentStatus']);
                    if($documentStatus instanceof LandlordDocumentStatus){
                        $detailsStr .= $documentStatus->getName();
                    }
                }
                break;
            case 6:
                if(isset($details['leaseDocumentStatus']) && !empty($details['leaseDocumentStatus'])
                    && isset($details['leaseDocumentDay']) && !empty($details['leaseDocumentDay'])
                ){
                    $detailsStr = $details['leaseDocumentDay']. ' days after ';
                    $documentStatus = $em->getRepository(LandlordDocumentStatus::class)->find($details['leaseDocumentStatus']);
                    if($documentStatus instanceof LandlordDocumentStatus){
                        $detailsStr .= $documentStatus->getName();
                    }
                }
                break;
            case 7:
                if(isset($details['siteStatus']) && !empty($details['siteStatus'])
                    && isset($details['siteDay']) && !empty($details['siteDay'])
                ){
                    $detailsStr = $details['siteDay']. ' days after ';
                    $siteStatus = $em->getRepository(ManagementStatus::class)->find($details['siteStatus']);
                    if($siteStatus instanceof ManagementStatus){
                        $detailsStr .= $siteStatus->getName();
                    }
                }
                break;
            case 8:
                if(isset($details['financialStatus']) && !empty($details['financialStatus'])
                    && isset($details['financialDay']) && !empty($details['financialDay'])
                ){
                    $detailsStr = $details['financialDay']. ' days after ';
                    $financialStatus = $em->getRepository(FinancialStatus::class)->find($details['financialStatus']);
                    if($financialStatus instanceof FinancialStatus){
                        $detailsStr .= $financialStatus->getName();
                    }
                }
                break;
        }

        return [
            'id' => $reminder->getId(),
            'type' => $reminder->getType(),
            'details' => $detailsStr,
            'delivery' => $delivery,
        ];
    }

    /**
     * @param EntityManager $em
     * @param $actualReminders
     * @return array
     * @throws \Exception
     */
    public static function generateActualRemindersDataForList(EntityManager $em, $actualReminders){
        $result = [];
        if(!empty($actualReminders)){
            foreach ($actualReminders as $actualReminder){
                if($actualReminder instanceof ActualReminder){

                    $result[] = self::generateActualReminderDataForList($em, $actualReminder);
                }
            }
        }

        return $result;
    }

    /**
     * @param EntityManager $em
     * @param ActualReminder $actualReminder
     * @return array
     * @throws \Exception
     */
    public static function generateActualReminderDataForList(EntityManager $em, ActualReminder $actualReminder){
        $site = null;
        if($actualReminder->getSite() instanceof Site){
            $site = $actualReminder->getSite();
        }
        elseif ($actualReminder->getLease() instanceof Lease){
            $site = $actualReminder->getLease()->getSite();
        }
        elseif($actualReminder->getLandlord() instanceof Landlord){
            $lease = $em->getRepository(Lease::class)->findOneBy(['landlord'=>$actualReminder->getLandlord()],['id'=>'DESC']);
            if($lease instanceof Lease){
                $site = $lease->getSite();
            }
        }

        $lease = null;
        if($actualReminder->getLease() instanceof Lease){
            $lease = $actualReminder->getLease();
        }
        elseif ($actualReminder->getSite() instanceof Site){
            $lease = $em->getRepository(Lease::class)->findOneBy(['site'=>$actualReminder->getSite()], ['id'=>'DESC']);
        }
        elseif ($actualReminder->getLandlord() instanceof Landlord){
            $lease = $em->getRepository(Lease::class)->findOneBy(['landlord'=>$actualReminder->getLandlord()], ['id'=>'DESC']);
        }

        $landlord = null;
        if($actualReminder->getLandlord() instanceof Landlord){
            $landlord = $actualReminder->getLandlord();
        }
        elseif ($actualReminder->getLease() instanceof Lease){
            $landlord = $actualReminder->getLease()->getLandlord();
        }
        elseif ($actualReminder->getSite() instanceof Site){
            $lease = $em->getRepository(Lease::class)->findOneBy(['site'=>$actualReminder->getSite()], ['id'=>'DESC']);
            if($lease instanceof Lease){
                $landlord = $lease->getLandlord();
            }
        }


        $currentCost = 0;
        if($lease instanceof Lease){
            $currentCost = LeaseHelper::getCurrentTotalMonthlyCost($em, $lease);
        }

        return [
            'id' => $actualReminder->getId(),
            'reminder' => $actualReminder->getReminder(),
            'site' => $site,
            'lease' => $lease,
            'landlord' => $landlord,
            'issue' => $actualReminder->getIssue(),
            'currentCost' => $currentCost,
            'status' => $actualReminder->getStatus(),
            'snoozeDate' => $actualReminder->getSnoozeDate(),
            'created' => $actualReminder->getCreated()
        ];
    }


}
