<?php


namespace App\Service;


use App\Entity\ActualReminder;
use App\Entity\Financial;
use App\Entity\FinancialStatus;
use App\Entity\Issue;
use App\Entity\Landlord;
use App\Entity\LandlordContact;
use App\Entity\LandlordDocumentStatus;
use App\Entity\Lease;
use App\Entity\LeaseElectricityType;
use App\Entity\ManagementStatus;
use App\Entity\Reminder;
use App\Entity\Site;
use App\Entity\User;
use App\Helper\LeaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ReminderService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var RequestStack
     */
    private $request;
    /**
     * @var Environment
     */
    private $templating;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * ReminderService constructor.
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @param RequestStack $request
     * @param Environment $templating
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, RequestStack $request, Environment $templating, UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->request = $request;
        $this->templating = $templating;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @throws \Exception
     */
    public function handleReminders(): void{
        $reminders = $this->em->getRepository(Reminder::class)->findAll();
        if(!empty($reminders)){
            foreach ($reminders as $reminder){
                if($reminder instanceof Reminder){
                    $this->generateReminderByType($reminder);
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByType(Reminder $reminder): void{
        switch ($reminder->getType()){
            case 1:
                $this->generateReminderByTypeLeaseExpiry($reminder);
                break;
            case 2:
                $this->generateReminderByTypeLeaseRenewalOption($reminder);
                break;
            case 3:
                $this->generateReminderByTypeLeaseEscalation($reminder);
                break;
            case 4:
                $this->generateReminderByTypeIssue($reminder);
                break;
            case 5:
                $this->generateReminderByTypeLandlordDocuments($reminder);
                break;
            case 6:
                $this->generateReminderByTypeLeaseDocuments($reminder);
                break;
            case 7:
                $this->generateReminderByTypeSiteStatus($reminder);
                break;
            case 8:
                $this->generateReminderByTypeFinancialStatus($reminder);
                break;
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeLeaseExpiry(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['leaseExpiry']) && !empty($details['leaseExpiry'])){
            $leases = $this->em->getRepository(Lease::class)->findByParamsForReminders(['expiry'=>$details['leaseExpiry']]);
            if(!empty($leases)){
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        $actualReminder = new ActualReminder($reminder, $lease->getSite(), $lease, $lease->getLandlord());
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeLeaseRenewalOption(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['leaseRenewal']) && !empty($details['leaseRenewal'])){
            $leases = $this->em->getRepository(Lease::class)->findByParamsForReminders(['renewal'=>$details['leaseRenewal']]);
            if(!empty($leases)){
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        $actualReminder = new ActualReminder($reminder, $lease->getSite(), $lease, $lease->getLandlord());
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeLeaseEscalation(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['leaseEscalation']) && !empty($details['leaseEscalation'])){
            $leases = $this->em->getRepository(Lease::class)->findByParamsForReminders();
            if(!empty($leases)){
                $now = new \DateTime();
                $now->setTime(0,0,0);
                foreach ($leases as $lease){
                    if($lease instanceof Lease && !empty($lease->getAnnualEscalationDate())){
                        $startDate = $lease->getStartDate();
                        if($startDate instanceof \DateTime){
                            if($lease->getAnnualEscalationDate() == '12month'){
                                $startDate->modify('+1 year');
                            }
                            else{
                                if($startDate->format('m') >= $lease->getAnnualEscalationDate()){
                                    $startDate->setDate($startDate->format('Y')+1, $lease->getAnnualEscalationDate(), $startDate->format('d'));
                                }
                                else{
                                    $startDate->setDate($startDate->format('Y'), $lease->getAnnualEscalationDate(), $startDate->format('d'));
                                }
                            }
                            if($startDate < $now){
                                $startDate->setDate($now->format('Y'), $startDate->format('m'), $startDate->format('d'));
                                if($startDate < $now){
                                    $startDate->modify('+1 year');
                                }
                            }

                            $diff = $startDate->diff($now);
                            if($diff->days == $details['leaseEscalation']){
                                $actualReminder = new ActualReminder($reminder, $lease->getSite(), $lease, $lease->getLandlord());
                                $this->em->persist($actualReminder);
                                $this->em->flush();

                                $this->sendReminders($actualReminder);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeIssue(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['issue']) && !empty($details['issue']) && $details['issue'] != 'immediately'){
            $issues = $this->em->getRepository(Issue::class)->findByParamsForReminders(['logged'=>$details['issue']]);
            if(!empty($issues)){
                foreach ($issues as $issue){
                    if($issue instanceof Issue){
                        $actualReminder = new ActualReminder($reminder, $issue->getSite(), null, null, $issue);
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeLandlordDocuments(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['landlordDocumentStatus']) && !empty($details['landlordDocumentStatus'])
            && isset($details['landlordDocumentDay']) && !empty($details['landlordDocumentDay'])
        ){
            $landlords = $this->em->getRepository(Landlord::class)->findByParamsForReminders([
                'documentStatus'=>$details['landlordDocumentStatus'],
                'documentStatusUpdated'=>$details['landlordDocumentDay']
            ]);
            if(!empty($landlords)){
                foreach ($landlords as $landlord){
                    if($landlord instanceof Landlord){
                        $actualReminder = new ActualReminder($reminder, null, null, $landlord);
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeLeaseDocuments(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['leaseDocumentStatus']) && !empty($details['leaseDocumentStatus'])
            && isset($details['leaseDocumentDay']) && !empty($details['leaseDocumentDay'])
        ){
            $leases = $this->em->getRepository(Lease::class)->findByParamsForReminders([
                'documentStatus'=>$details['leaseDocumentStatus'],
                'documentStatusUpdated'=>$details['leaseDocumentDay']
            ]);
            if(!empty($leases)){
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        $actualReminder = new ActualReminder($reminder, $lease->getSite(), $lease, $lease->getLandlord());
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeSiteStatus(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['siteStatus']) && !empty($details['siteStatus'])
            && isset($details['siteDay']) && !empty($details['siteDay'])
        ){
            $sites = $this->em->getRepository(Site::class)->findByParamsForReminders([
                'siteStatus'=>$details['siteStatus'],
                'siteStatusUpdated'=>$details['siteDay']
            ]);
            if(!empty($sites)){
                foreach ($sites as $site){
                    if($site instanceof Site){
                        $actualReminder = new ActualReminder($reminder, $site);
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function generateReminderByTypeFinancialStatus(Reminder $reminder): void{
        $details = $reminder->getDetail();
        if(isset($details['financialStatus']) && !empty($details['financialStatus'])
            && isset($details['financialDay']) && !empty($details['financialDay'])
        ){
            $financialItems = $this->em->getRepository(Financial::class)->findByParamsForReminders([
                'leasePaymentStatus'=>$details['financialStatus'],
                'leasePaymentStatusUpdated'=>$details['financialDay'],
//                'electricityPaymentStatus'=>$details['financialStatus'],
//                'electricityPaymentStatusUpdated'=>$details['financialDay'],
//                'otherCostPaymentStatus'=>$details['financialStatus'],
//                'otherCostPaymentStatusUpdated'=>$details['financialDay'],
            ]);
            if(!empty($financialItems)){
                foreach ($financialItems as $financial){
                    if($financial instanceof Financial){
                        $actualReminder = new ActualReminder($reminder, $financial->getLease()->getSite(), $financial->getLease(), $financial->getLease()->getLandlord());
                        $this->em->persist($actualReminder);
                        $this->em->flush();

                        $this->sendReminders($actualReminder);
                    }
                }
            }
        }
    }

    /**
     * @param ActualReminder $actualReminder
     * @throws \Exception
     */
    public function sendReminders(ActualReminder $actualReminder): void{
        $reminder = $actualReminder->getReminder();
        if($reminder->getEmailStatus()){
            if(!empty($reminder->getEmails())){
                $emails = $reminder->getEmails();
                if(isset($emails['users']) && !empty($emails['users'])){
                    foreach ($emails['users'] as $userID){
                        $user = $this->em->getRepository(User::class)->find($userID);
                        if($user instanceof User){
                            $this->sendEmail($actualReminder, $user->getEmail(), $user->getFirstName());
                        }
                    }
                }
                if(isset($emails['emails']) && !empty($emails['emails'])){
                    foreach ($emails['emails'] as $email){
                        if(!empty($email)){
                            $this->sendEmail($actualReminder, $email);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ActualReminder $actualReminder
     * @param $emailTo
     * @param string $firstName
     * @throws \Exception
     */
    public function sendEmail(ActualReminder $actualReminder, $emailTo, $firstName=''): void{
        $title = null;
        $data = null;
        $viewPath = null;
        switch ($actualReminder->getReminder()->getType()){
            case 1:
                $title = 'Reminder: Lease Expiry';
                $viewPath = 'emails/lease_expiry.html.twig';

                $lease = $actualReminder->getLease();
                if($lease instanceof Lease){
                    $landlordContact1 = null;
                    $landlordContact2 = null;
                    $landlordContacts = $this->em->getRepository(LandlordContact::class)->findBy(['landlord'=>$lease->getLandlord()]);
                    if(isset($landlordContacts[0]) && $landlordContacts[0] instanceof LandlordContact){
                        $landlordContact1 = $landlordContacts[0]->getFirstName()." ".$landlordContacts[0]->getLastName()." ".$landlordContacts[0]->getMobile()." ".$landlordContacts[0]->getEmail();
                    }
                    if(isset($landlordContacts[1]) && $landlordContacts[1] instanceof LandlordContact){
                        $landlordContact2 = $landlordContacts[1]->getFirstName()." ".$landlordContacts[1]->getLastName()." ".$landlordContacts[1]->getMobile()." ".$landlordContacts[1]->getEmail();
                    }

                    if($lease->getFrequencyOfLeasePayments() == 'monthly'){
                        $frequencyPayment = 'Monthly';
                    }
                    else{
                        $frequencyPayment = 'Annually';
                    }
                    $data = [
                        'siteNumber' => $lease->getSite()->getNumber(),
                        'siteName' => $lease->getSite()->getName(),
                        'landlordName' => $lease->getLandlord()->getName(),
                        'landlordContact1' => $landlordContact1,
                        'landlordContact2' => $landlordContact2,
                        'leaseStartDate' => $lease->getStartDate()->format('d M Y'),
                        'leaseEndDate' => $lease->getEndDate()->format('d M Y'),
                        'currentEscalation' => LeaseHelper::getLeaseRentalPercentage($this->em, $lease),
                        'frequencyPayment' => $frequencyPayment,
                        'electricityType' => ($lease->getElectricityType() instanceof LeaseElectricityType) ? $lease->getElectricityType()->getName() : null,
                        'currentRental' => LeaseHelper::getCurrentTotalMonthlyCost($this->em, $lease)
                    ];
                }
                break;
            case 2:
                $title = 'Reminder: Lease Renewal Option';
                $viewPath = 'emails/lease_renewal.html.twig';

                $lease = $actualReminder->getLease();
                if($lease instanceof Lease) {
                    $landlordContact1 = null;
                    $landlordContact2 = null;
                    $landlordContacts = $this->em->getRepository(LandlordContact::class)->findBy(
                        ['landlord' => $lease->getLandlord()]
                    );
                    if (isset($landlordContacts[0]) && $landlordContacts[0] instanceof LandlordContact) {
                        $landlordContact1 = $landlordContacts[0]->getFirstName()." ".$landlordContacts[0]->getLastName()." ".$landlordContacts[0]->getMobile()." ".$landlordContacts[0]->getEmail();
                    }
                    if (isset($landlordContacts[1]) && $landlordContacts[1] instanceof LandlordContact) {
                        $landlordContact2 = $landlordContacts[1]->getFirstName()." ".$landlordContacts[1]->getLastName()." ".$landlordContacts[1]->getMobile()." ".$landlordContacts[1]->getEmail();
                    }

                    if ($lease->getFrequencyOfLeasePayments() == 'monthly') {
                        $frequencyPayment = 'Monthly';
                    } else {
                        $frequencyPayment = 'Annually';
                    }
                    $renewalDate = new \DateTime($lease->getEndDate()->format('Y-m-d'));
                    $renewalDate->modify('-'.$lease->getRenewal().' days');

                    $data = [
                        'siteNumber' => $lease->getSite()->getNumber(),
                        'siteName' => $lease->getSite()->getName(),
                        'landlordName' => $lease->getLandlord()->getName(),
                        'landlordContact1' => $landlordContact1,
                        'landlordContact2' => $landlordContact2,
                        'leaseStartDate' => $lease->getStartDate()->format('d M Y'),
                        'leaseEndDate' => $lease->getEndDate()->format('d M Y'),
                        'currentEscalation' => LeaseHelper::getLeaseRentalPercentage($this->em, $lease),
                        'frequencyPayment' => $frequencyPayment,
                        'electricityType' => ($lease->getElectricityType() instanceof LeaseElectricityType) ? $lease->getElectricityType()->getName() : null,
                        'currentRental' => LeaseHelper::getCurrentTotalMonthlyCost($this->em, $lease),
                        'renewalDate' => $renewalDate->format('d M Y')
                    ];
                }
                break;
            case 3:
                $title = 'Reminder: Lease Escalation';
                $viewPath = 'emails/lease_escalation.html.twig';

                $lease = $actualReminder->getLease();
                if($lease instanceof Lease) {
                    $now = new \DateTime();
                    $now->setTime(0,0,0);
                    $startDate = $lease->getStartDate();
                    if($startDate instanceof \DateTime){
                        if($lease->getAnnualEscalationDate() == '12month'){
                            $startDate->modify('+1 year');
                        }
                        else{
                            if($startDate->format('m') >= $lease->getAnnualEscalationDate()){
                                $startDate->setDate($startDate->format('Y')+1, $lease->getAnnualEscalationDate(), $startDate->format('d'));
                            }
                            else{
                                $startDate->setDate($startDate->format('Y'), $lease->getAnnualEscalationDate(), $startDate->format('d'));
                            }
                        }
                        if($startDate < $now){
                            $startDate->setDate($now->format('Y'), $startDate->format('m'), $startDate->format('d'));
                            if($startDate < $now){
                                $startDate->modify('+1 year');
                            }
                        }

                        $data = [
                            'siteNumber' => $lease->getSite()->getNumber(),
                            'siteName' => $lease->getSite()->getName(),
                            'currentEscalation' => LeaseHelper::getLeaseRentalPercentage($this->em, $lease),
                            'currentRental' => LeaseHelper::getCurrentTotalMonthlyCost($this->em, $lease),
                            'newRental' => LeaseHelper::getCurrentTotalMonthlyCost($this->em, $lease, $startDate)
                        ];
                    }
                }
                break;
            case 4:
                $title = 'Reminder: Outstanding Issue';
                $viewPath = 'emails/issue.html.twig';

                $issue = $actualReminder->getIssue();
                if($issue instanceof Issue){
                    $data = [
                        'siteNumber' => $issue->getSite()->getNumber(),
                        'siteName' => $issue->getSite()->getName(),
                        'issue' => $issue,
                        'link' => $this->urlGenerator->generate('issue_edit', ['id'=>$issue->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                    ];
                }
                break;
            case 5:
                $title = 'Reminder: Missing Supporting Documents | Landlord';
                $viewPath = 'emails/landlord_document.html.twig';

                $landlord = $actualReminder->getLandlord();
                if($landlord instanceof Landlord){
                    $data = [
                        'landlordName' => $landlord->getName(),
                        'documentType' => ($landlord->getDocumentStatus() instanceof LandlordDocumentStatus) ? $landlord->getDocumentStatus()->getName() : null,
                        'link' => $this->urlGenerator->generate('landlord_edit', ['id'=>$landlord->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                    ];
                }
                break;
            case 6:
                $title = 'Reminder: Missing Supporting Documents | Lease';
                $viewPath = 'emails/lease_document.html.twig';

                $lease = $actualReminder->getLease();
                if($lease instanceof Lease){
                    $data = [
                        'siteNumber' => $lease->getSite()->getNumber(),
                        'siteName' => $lease->getSite()->getName(),
                        'documentType' => ($lease->getDocumentStatus() instanceof LandlordDocumentStatus) ? $lease->getDocumentStatus()->getName() : null,
                        'link' => $this->urlGenerator->generate('lease_edit', ['id'=>$lease->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                    ];
                }
                break;
            case 7:
                $viewPath = 'emails/site_status.html.twig';

                $site = $actualReminder->getSite();
                if($site instanceof Site && $site->getSiteStatus() instanceof ManagementStatus){
                    $title = 'Reminder | Site Status: '.$site->getSiteStatus()->getName();
                    $data = [
                        'siteNumber' => $site->getNumber(),
                        'siteName' => $site->getName(),
                        'siteStatus' => ($site->getSiteStatus() instanceof ManagementStatus) ? $site->getSiteStatus()->getName() : null,
                        'siteStatusUpdate' => $site->getSiteStatusUpdated()->format('d M Y'),
                        'link' => $this->urlGenerator->generate('site_edit', ['id'=>$site->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                    ];
                }
                break;
            case 8:
                $viewPath = 'emails/financial.html.twig';

                $details = $actualReminder->getReminder()->getDetail();
                if(isset($details['financialStatus']) && !empty($details['financialStatus'])){
                    $financialStatus = $this->em->getRepository(FinancialStatus::class)->find($details['financialStatus']);
                    if($financialStatus instanceof FinancialStatus){
                        $title = 'Reminder | '.$financialStatus->getName();
                        switch ($financialStatus->getId()){
                            case 1:
                                $data = [
                                    'text' => 'Your invoice has been received.'
                                ];
                                break;
                            case 2:
                                $data = [
                                    'text' => 'Your invoice has been sent for payment.'
                                ];
                                break;
                            case 3:
                                $data = [
                                    'text' => 'Your invoice has been paid.'
                                ];
                                break;
                            case 4:
                                $data = [
                                    'text' => 'Your invoice has been paid prior to invoice delivery.'
                                ];
                                break;
                        }
                    }
                }
                break;
        }

        if(!empty($title) && !empty($viewPath) && !empty($data)){
            $data['firstName'] = $firstName;

            try{
                $message = (new \Swift_Message($title))
                    ->setFrom($_ENV['MAILER_FROM'], $_ENV['MAILER_FROM_NAME'])
                    ->setTo($emailTo)
                    ->setBody($this->templating->render($viewPath, $data), 'text/html');
                $this->mailer->send($message);
            }
            catch (\Exception $e){}
        }

    }
}
