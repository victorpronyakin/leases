<?php


namespace App\Service;


use App\Entity\ContactReminder;
use App\Entity\LandlordContact;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ContactReminderService
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
     * @var RouterInterface
     */
    private $router;

    /**
     * ContactReminderService constructor.
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @param RequestStack $request
     * @param Environment $templating
     * @param RouterInterface $router
     */
    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, RequestStack $request, Environment $templating, RouterInterface $router)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->request = $request;
        $this->templating = $templating;
        $this->router = $router;
    }

    /**
     * @throws \Exception
     */
    public function handler(): void{
        $contactReminders = $this->em->getRepository(ContactReminder::class)->findForSend();
        if(!empty($contactReminders)){
            foreach ($contactReminders as $contactReminder){
                if($contactReminder instanceof ContactReminder){
                    if($contactReminder->getType() == 1){
                        $this->handlerAllContacts($contactReminder);
                    }
                    else if($contactReminder->getType() == 2){
                        $this->handlerEmergencyContacts($contactReminder);
                    }
                    $contactReminder->setDateLastSend(new \DateTime());
                    $contactReminder->setDateNextSend(new \DateTime('+'.$contactReminder->getMonth().' months'));
                    $this->em->persist($contactReminder);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @param ContactReminder $contactReminder
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function handlerAllContacts(ContactReminder $contactReminder): void{
        $contacts = $this->em->getRepository(LandlordContact::class)->findAll();
        foreach ($contacts as $contact){
            if($contact instanceof LandlordContact){
                $messageText = $this->replaceTextMessage($contact, $contactReminder->getText());
                $noLongerLink = $this->router->generate('public_reminder_no_longer_contact', ['type'=>'contact', 'id'=>$contact->getId()], 0);
                $this->sendEmail($contact, $contactReminder, $messageText, $noLongerLink);
            }
        }
    }

    /**
     * @param ContactReminder $contactReminder
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function handlerEmergencyContacts(ContactReminder $contactReminder): void {
        $sites = $this->em->getRepository(Site::class)->findAll();
        foreach ($sites as $site){
            if($site instanceof Site){
                if($site->getPrimaryEmergencyContact() instanceof LandlordContact){
                    $messageText = $this->replaceTextMessage($site->getPrimaryEmergencyContact(), $contactReminder->getText(), $site);
                    $noLongerLink = $this->router->generate('public_reminder_no_longer_emergency_contact', ['type'=>'primary', 'siteId'=>$site->getId()], 0);
                    $this->sendEmail($site->getPrimaryEmergencyContact(), $contactReminder, $messageText, $noLongerLink);
                }

                if($site->getSecondaryEmergencyContact() instanceof LandlordContact){
                    $messageText = $this->replaceTextMessage($site->getSecondaryEmergencyContact(), $contactReminder->getText(), $site);
                    $noLongerLink = $this->router->generate('public_reminder_no_longer_emergency_contact', ['type'=>'secondary', 'siteId'=>$site->getId()], 0);
                    $this->sendEmail($site->getSecondaryEmergencyContact(), $contactReminder, $messageText, $noLongerLink);
                }
            }
        }

    }

    /**
     * @param $contact
     * @param $text
     * @param Site|null $site
     * @return string
     */
    public function replaceTextMessage(LandlordContact $contact, $text, Site $site = null): string {
        $siteNumber = ''; $siteName = ''; $siteAddress = ''; $siteCity = '';
        $name = $contact->getFirstName();
        $surname = $contact->getLastName();
        $email = $contact->getEmail();
        $cell = $contact->getMobile();
        if($site instanceof Site){
            $siteNumber = $site->getNumber();
            $siteName = $site->getName();
            $siteAddress = $site->getAddress();
            $siteCity = $site->getCity();
        }

        $text = str_replace('{NAME}', $name, $text);
        $text = str_replace('{SURNAME}', $surname, $text);
        $text = str_replace('{EMAIL}', $email, $text);
        $text = str_replace('{CELL}', $cell, $text);
        $text = str_replace('{SITE_NUMBER}', $siteNumber, $text);
        $text = str_replace('{SITE_NAME}', $siteName, $text);
        $text = str_replace('{SITE_ADDRESS}', $siteAddress, $text);
        $text = str_replace('{SITE_CITY}', $siteCity, $text);

        return nl2br($text);
    }


    /**
     * @param LandlordContact $contact
     * @param ContactReminder $contactReminder
     * @param $messageText
     * @param $noLongerLink
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendEmail(LandlordContact $contact, ContactReminder $contactReminder, $messageText, $noLongerLink): void{

        $message = (new \Swift_Message(($contactReminder->getType() == 1) ? 'Contact Update Reminder' : 'Emergency Contact Update Reminder'))
            ->setFrom($_ENV['MAILER_FROM'], $_ENV['MAILER_FROM_NAME'])
            ->setTo($contact->getEmail())
            ->setBody(
                $this->templating->render('emails/contacts_update.html.twig', [
                    'messageText' => $messageText,
                    'contactReminder' => $contactReminder,
                    'allCorrectLink' => $this->router->generate('public_reminder_type',['type'=>'all_correct'], 0),
                    'noLongerLink' => $noLongerLink
                ]),
                'text/html'
            );
        try{
            $this->mailer->send($message);
        }
        catch (\Exception $e){}
    }
}
