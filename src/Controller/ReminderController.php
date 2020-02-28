<?php

namespace App\Controller;

use App\Entity\ActualReminder;
use App\Entity\ContactReminder;
use App\Entity\FinancialStatus;
use App\Entity\Issue;
use App\Entity\LandlordDocumentStatus;
use App\Entity\ManagementStatus;
use App\Entity\Reminder;
use App\Entity\User;
use App\Helper\ReminderHelper;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ReminderController
 * @package App\Controller
 *
 * @Route("/reminder")
 */
class ReminderController extends AbstractController
{
    /**
     * @var SettingService
     */
    public $settingService;

    /**
     * SettingsController constructor.
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/", name="reminders_list", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        $remindersResult = $em->getRepository(Reminder::class)->findAll();
        $reminders = ReminderHelper::generateRemindersDataForList($em, $remindersResult);

        $activeRemindersResult = $em->getRepository(ActualReminder::class)->getAllByParams(true);
        $activeReminders = ReminderHelper::generateActualRemindersDataForList($em, $activeRemindersResult);
        $historicalRemindersResult = $em->getRepository(ActualReminder::class)->getAllByParams(false);
        $historicalReminders = ReminderHelper::generateActualRemindersDataForList($em, $historicalRemindersResult);
        $snoozedRemindersResult = $em->getRepository(ActualReminder::class)->getAllByParams(true, false, true);
        $snoozedReminders = ReminderHelper::generateActualRemindersDataForList($em, $snoozedRemindersResult);

        $contactReminders = $em->getRepository(ContactReminder::class)->findAll();

        return $this->render('reminder/index.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'reminders',
            'reminders' => $reminders,
            'activeReminders' => $activeReminders,
            'historicalReminders' => $historicalReminders,
            'snoozedReminders' => $snoozedReminders,
            'contactReminders' => $contactReminders,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Reminder')
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/setup", name="reminders_setup", methods={"GET","POST"})
     */
    public function setUpAction(Request $request, ValidatorInterface $validator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'added')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $reminder = new Reminder();
        if($request->request->has('submit')){
            $reminder->update($request->request->all());

            $errors = $validator->validate($reminder);
            if(count($errors) === 0){
                if(!$this->checkExistReminder($em, $reminder)){
                    $em->persist($reminder);
                    $em->flush();

                    $this->addFlash('success', 'Reminder has been set up!');
                    return $this->redirectToRoute('reminders_list');
                }
                else{
                    $this->addFlash('error', 'Reminder already exists');
                }
            }
            else {
                foreach ($errors as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        $documentStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();
        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();
        $financialStatuses = $em->getRepository(FinancialStatus::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('reminder/setup.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'reminders',
            'reminder' => $reminder,
            'documentStatuses' => $documentStatuses,
            'siteStatuses' => $siteStatuses,
            'financialStatuses' => $financialStatuses,
            'users' => $users,
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param $id
     * @return RedirectResponse|Response
     *
     * @Route("/edit/{id}", name="reminders_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function editByIdAction(Request $request, ValidatorInterface $validator, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'viewable') && !UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        $reminder = $em->getRepository(Reminder::class)->find($id);
        if(!$reminder instanceof Reminder){
            $this->addFlash('error', 'Reminder not found');
            return $this->redirectToRoute('reminders_list');
        }

        if($request->request->has('submit')) {
            if (UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'editable')) {
                $reminder->update($request->request->all());

                $errors = $validator->validate($reminder);
                if(count($errors) === 0){
                    if(!$this->checkExistReminder($em, $reminder)){
                        $em->persist($reminder);
                        $em->flush();

                        $this->addFlash('success', 'Reminder has been set up!');
                    }
                    else{
                        $this->addFlash('error', 'Reminder already exists');
                    }
                }
                else {
                    foreach ($errors as $error){
                        $this->addFlash('error', $error->getMessage());
                    }
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }

        $documentStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();
        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();
        $financialStatuses = $em->getRepository(FinancialStatus::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('reminder/edit.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'reminders',
            'reminder' => $reminder,
            'documentStatuses' => $documentStatuses,
            'siteStatuses' => $siteStatuses,
            'financialStatuses' => $financialStatuses,
            'users' => $users,
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", name="reminders_remove", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function removeByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'removable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('reminders_list');
        }
        $reminder = $em->getRepository(Reminder::class)->find($id);
        if($reminder instanceof Reminder){
            $em->remove($reminder);
            $em->flush();

            $this->addFlash('success', 'Reminder has been removed');
        }

        return $this->redirectToRoute('reminders_list');
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws \Exception
     *
     * @Route("/contact/edit/{id}", name="reminders_contact_edit", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function editContactReminderById(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('reminders_list');
        }

        $contactReminder = $em->getRepository(ContactReminder::class)->find($id);
        if($contactReminder instanceof ContactReminder){
            $oldMonth = $contactReminder->getMonth();
            $error = false;
            if($request->request->has('month') && $request->request->get('month')>0){
                $contactReminder->setMonth($request->request->get('month'));
            }
            else{
                $error = true;
                $this->addFlash('error', 'Month number is required and must be greater than 0');
            }
            if($request->request->has('text') && !empty($request->request->get('text'))){
                $contactReminder->setText($request->request->get('text'));
            }
            else{
                $error = true;
                $this->addFlash('error', 'Message is required');
            }
            if($request->request->has('status')){
                $contactReminder->setStatus(true);
            }
            else{
                $contactReminder->setStatus(false);
            }
            if($request->request->has('allCorrect')){
                $contactReminder->setAllCorrect(true);
            }
            else{
                $contactReminder->setAllCorrect(false);
            }
            if($request->request->has('updateInfo')){
                $contactReminder->setUpdateInfo(true);
            }
            else{
                $contactReminder->setUpdateInfo(false);
            }
            if($request->request->has('noLonger')){
                $contactReminder->setNoLonger(true);
            }
            else{
                $contactReminder->setNoLonger(false);
            }

            if($contactReminder->getUpdateInfo() == true){
                if($request->request->has('updateInfoEmail') && !empty($request->request->get('updateInfoEmail'))){
                    $contactReminder->setUpdateInfoEmail($request->request->get('updateInfoEmail'));
                }
                else{
                    $error = true;
                    $this->addFlash('error', 'Email Address for Update info required is required');
                }
            }
            if(!$error){
                if($oldMonth != $contactReminder->getMonth()){
                    $lastSend = $contactReminder->getDateLastSend();
                    if($lastSend instanceof \DateTime){
                        $nextSend = $lastSend->modify('+'.$contactReminder->getMonth().' months');
                        $now = new \DateTime();
                        if($now->format('Y-m-d') < $nextSend->format('Y-m-d')){
                            $contactReminder->setDateNextSend($nextSend);
                        }
                        else{
                            $contactReminder->setDateNextSend($now->modify("+1 days"));
                        }
                    }
                }

                $em->persist($contactReminder);
                $em->flush();

                $this->addFlash('success', 'Contacts Update Reminder has been edit!');
            }
        }

        return $this->redirectToRoute('reminders_list');
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/actual/edit/{id}", name="reminders_actual_edit", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function editActualByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Reminder', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        $actualReminder = $em->getRepository(ActualReminder::class)->find($id);
        if($actualReminder instanceof ActualReminder){
            if($request->request->has('status') && in_array($request->request->get('status'), ['done', 'active'])){
                if($request->request->get('status') == 'done'){
                    $actualReminder->setStatus(false);
                }
                else{
                    $actualReminder->setSnoozeDate(null);
                }
                $em->persist($actualReminder);
                $em->flush();

                return new JsonResponse(['result'=>true]);
            }

            if($request->request->has('snoozeDate') && !empty($request->request->get('snoozeDate'))){
                $snoozeDate = new \DateTime($request->request->get('snoozeDate'));
                $actualReminder->setSnoozeDate($snoozeDate);
                $em->persist($actualReminder);
                $em->flush();

                return new JsonResponse(['result'=>true]);
            }

            if($request->request->has('action')){
                if($request->request->get('action') == 'closeIssue'){
                    if($actualReminder->getIssue() instanceof Issue){
                        $actualReminder->getIssue()->setStatus(false);
                        $em->persist($actualReminder->getIssue());
                        $em->flush();

                        return new JsonResponse(['result'=>true]);
                    }
                }
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param EntityManager $em
     * @param Reminder $reminder
     * @return bool
     */
    private function checkExistReminder(EntityManager $em, Reminder $reminder){
        $exist = false;
        $details = $reminder->getDetail();
        $existReminders = $em->getRepository(Reminder::class)->findBy(['type'=>$reminder->getType()]);
        foreach ($existReminders as $existReminder){
            if($existReminder instanceof Reminder && $existReminder->getId() != $reminder->getId()){
                $existDetails = $existReminder->getDetail();
                switch ($existReminder->getType()){
                    case 1:
                        if(isset($details['leaseExpiry']) && isset($existDetails['leaseExpiry']) && $details['leaseExpiry'] == $existDetails['leaseExpiry']){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 2:
                        if(isset($details['leaseRenewal']) && isset($existDetails['leaseRenewal']) && $details['leaseRenewal'] == $existDetails['leaseRenewal']){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 3:
                        if(isset($details['leaseEscalation']) && isset($existDetails['leaseEscalation']) && $details['leaseEscalation'] == $existDetails['leaseEscalation']){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 4:
                        if(isset($details['issue']) && isset($existDetails['issue']) && $details['issue'] == $existDetails['issue']){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 5:
                        if(
                            isset($details['landlordDocumentStatus']) && isset($existDetails['landlordDocumentStatus']) && $details['landlordDocumentStatus'] == $existDetails['landlordDocumentStatus']
                            && isset($details['landlordDocumentDay']) && isset($existDetails['landlordDocumentDay']) && $details['landlordDocumentDay'] == $existDetails['landlordDocumentDay']

                        ){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 6:
                        if(
                            isset($details['leaseDocumentStatus']) && isset($existDetails['leaseDocumentStatus']) && $details['leaseDocumentStatus'] == $existDetails['leaseDocumentStatus']
                            && isset($details['leaseDocumentDay']) && isset($existDetails['leaseDocumentDay']) && $details['leaseDocumentDay'] == $existDetails['leaseDocumentDay']

                        ){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 7:
                        if(
                            isset($details['siteStatus']) && isset($existDetails['siteStatus']) && $details['siteStatus'] == $existDetails['siteStatus']
                            && isset($details['siteDay']) && isset($existDetails['siteDay']) && $details['siteDay'] == $existDetails['siteDay']

                        ){
                            $exist = true;
                            break 2;
                        }
                        break;
                    case 8:
                        if(
                            isset($details['financialStatus']) && isset($existDetails['financialStatus']) && $details['financialStatus'] == $existDetails['financialStatus']
                            && isset($details['financialDay']) && isset($existDetails['financialDay']) && $details['financialDay'] == $existDetails['financialDay']

                        ){
                            $exist = true;
                            break 2;
                        }
                        break;
                }
            }
        }

        return $exist;
    }
}
