<?php

namespace App\Controller;

use App\Entity\Billing;
use App\Entity\Issue;
use App\Entity\Lease;
use App\Entity\LeaseNotes;
use App\Entity\ManagementStatus;
use App\Entity\Setting;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserType;
use App\Helper\LeaseHelper;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AgentController
 * @package App\Controller
 *
 * @Route("/agent")
 */
class AgentController extends AbstractController
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
     * @return Response
     * @throws \Exception
     *
     * @Route("/", name="agent_dashboard", methods={"GET"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }


        $leasesResult = $em->getRepository(Lease::class)->findLeaseByAgents();
        $leases = [];
        foreach ($leasesResult as $lease){
            if($lease instanceof Lease){
                $issueStatus = "No";
                $siteIssue = $em->getRepository(Issue::class)->count(['site'=>$lease->getSite(), 'status'=>true]);
                if($siteIssue > 0){
                    $issueStatus = 'Yes';
                }
                $notes = $em->getRepository(LeaseNotes::class)->findBy(['lease'=>$lease], ['updated'=>'DESC']);

                $leases[] = [
                    'id' => $lease->getId(),
                    'site' => $lease->getSite(),
                    'notes' => $notes,
                    'issueStatus' => $issueStatus,
                    'allocated' => $lease->getAllocated(),
                    'endDate' => $lease->getEndDate(),
                    'currentTotalCost' => LeaseHelper::getCurrentTotalMonthlyCost($em, $lease),
                    'currentEscalation' => LeaseHelper::getLeaseRentalPercentage($em, $lease),
                    'targetRenewalRental' => $lease->getTargetRenewalRental(),
                    'targetRenewalEscalation' => $lease->getTargetRenewalEscalation(),
                ];
            }
        }

        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();

        $mainAgentStatuses = [];
        $mainAgentSetting = $em->getRepository(Setting::class)->findOneBy(['name'=>'mainAgentStatus']);
        if($mainAgentSetting instanceof Setting){
            $mainAgentStatus = json_decode($mainAgentSetting->getValue(), true);
            if(!empty($mainAgentStatus)){
                foreach ($mainAgentStatus as $statusId){
                    $siteStatus = $em->getRepository(ManagementStatus::class)->find($statusId);
                    if($siteStatus instanceof ManagementStatus){
                        $mainAgentStatuses[] = [
                            'label' => $siteStatus->getName(),
                            'count' => $em->getRepository(Lease::class)->findCountLeaseByAgents($siteStatus->getId())
                        ];
                    }
                }
            }
        }

        return $this->render('agent/index.html.twig', [
            'active_menu1' => 'agents',
            'active_menu2' => 'agent_dashboard',
            'leases' => $leases,
            'siteStatuses' => $siteStatuses,
            'mainAgentStatuses' => $mainAgentStatuses,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Agent'),
            'userPermissionLease' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease'),
        ]);
    }

    /**
     * @param Request $request
     * @throws \Exception
     *
     * @Route("/dashboard_download", name="agent_dashboard_download", methods={"GET"})
     */
    public function dashboardDownloadAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Site ID')
            ->setCellValue('B1', 'Site Name')
            ->setCellValue('C1', 'Days lapsed since status update')
            ->setCellValue('D1', 'Active Issue')
            ->setCellValue('E1', "User Allocated to")
            ->setCellValue('F1', "Date of expiry(Days)")
            ->setCellValue('G1', "Current Total Rental")
            ->setCellValue('H1', "Current Escalation")
            ->setCellValue('I1', "Target Total Rental")
            ->setCellValue('J1', "Target Escalation");

        $now = new \DateTime();
        $siteNumber = null;
        if($request->query->has('number') && !empty($request->query->get('number'))){
            $siteNumber=$request->query->get('number');
        }
        $siteName = null;
        if($request->query->has('name') && !empty($request->query->get('name'))){
            $siteName=$request->query->get('name');
        }
        $siteStatus = null;
        if($request->query->has('status') && !empty($request->query->get('status'))){
            $status = $em->getRepository(ManagementStatus::class)->findOneBy(['name'=>$request->query->get('status')]);
            if($status instanceof ManagementStatus){
                $siteStatus=$status->getId();
            }
        }
        $currentSymbol = $this->settingService->getCurrencySymbol();
        $leases = $em->getRepository(Lease::class)->findLeaseByAgents(['number'=>$siteNumber, 'name'=>$siteName, 'status'=>$siteStatus]);
        if(!empty($leases)){
            $i = 1;
            foreach ($leases as $lease){
                if($lease instanceof Lease){
                    $i++;
                    $issueStatus = "No";
                    $siteIssue = $em->getRepository(Issue::class)->count(['site'=>$lease->getSite(), 'status'=>true]);
                    if($siteIssue > 0){
                        $issueStatus = 'Yes';
                    }
                    $daysStatusUpdated = $lease->getSite()->getSiteStatusUpdated()->diff($now)->days;
                    $daysExpire = $now->diff($lease->getEndDate())->days;

                    $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, $lease->getSite()->getNumber())
                        ->setCellValue('B'.$i, $lease->getSite()->getName())
                        ->setCellValue('C'.$i, $daysStatusUpdated)
                        ->setCellValue('D'.$i, $issueStatus)
                        ->setCellValue('E'.$i, ($lease->getAllocated() instanceof User) ? $lease->getAllocated()->getFirstName().' '.$lease->getAllocated()->getLastName() : '')
                        ->setCellValue('F'.$i, $lease->getEndDate()->format('d M Y')."(".$daysExpire.")")
                        ->setCellValue('G'.$i, $currentSymbol." ".LeaseHelper::getCurrentTotalMonthlyCost($em, $lease))
                        ->setCellValue('H'.$i, LeaseHelper::getLeaseRentalPercentage($em, $lease))
                        ->setCellValue('I'.$i, $currentSymbol." ".$lease->getTargetRenewalRental())
                        ->setCellValue('J'.$i, $lease->getTargetRenewalEscalation());
                }
            }
        }


        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Lease_Under_Management_'.$now->format('Y-m-d_H-i').'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/add", name="agent_add", methods={"GET","POST"})
     */
    public function addAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent', 'added')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $leasesResult = $em->getRepository(Lease::class)->findLeaseByAgents(['agentStatus'=>false]);
        $leases = [];
        foreach ($leasesResult as $lease){
            if($lease instanceof Lease){
                $notes = $em->getRepository(LeaseNotes::class)->findBy(['lease'=>$lease]);

                $leases[] = [
                    'id' => $lease->getId(),
                    'landlord' => $lease->getLandlord(),
                    'site' => $lease->getSite(),
                    'notes' => $notes,
                    'currentTotalCost' => LeaseHelper::getCurrentTotalMonthlyCost($em, $lease),
                    'proposedLease' => LeaseHelper::getProposedLeaseByLandlord($em, $lease),
                    'percentage' => LeaseHelper::getLeaseRentalPercentage($em, $lease),
                    'escalationSaving' => LeaseHelper::getEscalationSaving($em, $lease)
                ];
            }
        }

        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();
        $agentType = $em->getRepository(UserType::class)->findOneBy(['name'=>'Agent']);
        if($agentType instanceof UserType){
            $users = $em->getRepository(User::class)->findBy(['type'=>$agentType]);
        }
        else{
            $users = $em->getRepository(User::class)->findAll();
        }

        if($request->request->has('submit')){
            $lease = $em->getRepository(Lease::class)->find($request->request->get('leaseId'));
            if($lease instanceof Lease){
                $lease->setAgentStatus(true);
                $siteStatus = $em->getRepository(ManagementStatus::class)->find($request->request->get('siteStatus'));
                if($siteStatus instanceof ManagementStatus){
                    if($lease->getSite()->getSiteStatus()->getId() != $siteStatus->getId()){
                        $lease->getSite()->setSiteStatus($siteStatus);
                        $lease->getSite()->setSiteStatusUpdated(new \DateTime());
                        $em->persist($lease->getSite());
                    }
                    $user = $em->getRepository(User::class)->find($request->request->get('allocated'));
                    if($user instanceof User){
                        $lease->setAllocated($user);
                        $lease->setFee($request->request->get('fee'));
                        if($lease->getFee() == 1 || $lease->getFee() == 2){
                            $lease->setFeeValue($request->request->get('feeValue1'));
                        }
                        else{
                            $lease->setFeeValue($request->request->get('feeValue2'));
                        }
                        if($request->request->has('feeDurationCheckbox') && $request->request->get('feeDurationCheckbox') == 'all'){
                            $lease->setFeeDuration('all');
                        }
                        else{
                            $lease->setFeeDuration($request->request->get('feeDuration'));
                        }
                        $lease->setFeeEscalation($request->request->get('feeEscalation'));
                        $lease->setProposedLease($request->request->get('proposedLease'));
                        $lease->setProposedLeaseManually(($request->request->get('proposedLeaseManually') == 'on') ? true : false);
                        $lease->setTargetRenewalRental($request->request->get('targetRenewalRental'));
                        $lease->setTargetRenewalEscalation($request->request->get('targetRenewalEscalation'));
                        $em->persist($lease);

                        $errorStatus = false;
                        $useID = [];
                        if($request->request->has('notes') && !empty($request->request->get('notes'))){
                            foreach ($request->request->get('notes') as $note){
                                if(isset($note['text']) && !empty($note['text'])){
                                    $leaseNote = null;
                                    if(isset($note['id']) && !empty($note['id'])){
                                        $leaseNote = $em->getRepository(LeaseNotes::class)->find($note['id']);
                                    }
                                    if($leaseNote instanceof LeaseNotes){
                                        if($leaseNote->getText() != $note['text']){
                                            $leaseNote->setText($note['text']);
                                            $leaseNote->setUpdated(new \DateTime());
                                        }
                                        $useID[] = $leaseNote->getId();
                                    }
                                    else{
                                        $leaseNote = new LeaseNotes($lease, $note['text']);
                                    }
                                    $em->persist($leaseNote);
                                }
                                else{
                                    $this->addFlash('error', 'Note text is required');
                                    $errorStatus = true;
                                }
                            }
                        }
                        if($errorStatus == false){
                            $em->getRepository(LeaseNotes::class)->removeByLeaseInNot($lease, $useID);
                            $em->flush();

                            $this->addFlash('success', 'Site has been added to agent!');
                            return $this->redirectToRoute('agent_dashboard');
                        }
                    }
                    else{
                        $this->addFlash('error', 'User not found!');
                    }
                }
                else{
                    $this->addFlash('error', 'Site Status is required!');
                }
            }
            else{
                $this->addFlash('error', 'Lease not found!');
            }
        }

        return $this->render('agent/add.html.twig', [
            'active_menu1' => 'agents',
            'leases' => $leases,
            'siteStatuses' => $siteStatuses,
            'users' => $users,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/edit/{id}", name="agent_edit", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function editByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent', 'viewable') && !UserHelper::checkPermission($em, $this->getUser(), 'Agent', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $lease = $em->getRepository(Lease::class)->find($id);
        if(!$lease instanceof Lease){
            $this->addFlash('error', 'Lease not found!');
            return $this->redirectToRoute('agent_dashboard');
        }

        if($request->request->has('submit')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Agent', 'editable')) {
                $siteStatus = $em->getRepository(ManagementStatus::class)->find($request->request->get('siteStatus'));
                if($siteStatus instanceof ManagementStatus){
                    if($lease->getSite()->getSiteStatus()->getId() != $siteStatus->getId()){
                        $lease->getSite()->setSiteStatus($siteStatus);
                        $lease->getSite()->setSiteStatusUpdated(new \DateTime());
                        $em->persist($lease->getSite());
                    }
                    $user = $em->getRepository(User::class)->find($request->request->get('allocated'));
                    if($user instanceof User){
                        $lease->setAllocated($user);
                        $lease->setFee($request->request->get('fee'));
                        if($lease->getFee() == 1 || $lease->getFee() == 2){
                            $lease->setFeeValue($request->request->get('feeValue1'));
                        }
                        else{
                            $lease->setFeeValue($request->request->get('feeValue2'));
                        }
                        if($request->request->has('feeDurationCheckbox') && $request->request->get('feeDurationCheckbox') == 'all'){
                            $lease->setFeeDuration('all');
                        }
                        else{
                            $lease->setFeeDuration($request->request->get('feeDuration'));
                        }
                        $lease->setFeeEscalation($request->request->get('feeEscalation'));
                        $lease->setProposedLease($request->request->get('proposedLease'));
                        $lease->setProposedLeaseManually(($request->request->get('proposedLeaseManually') == 'on') ? true : false);
                        $lease->setTargetRenewalRental($request->request->get('targetRenewalRental'));
                        $lease->setTargetRenewalEscalation($request->request->get('targetRenewalEscalation'));
                        $em->persist($lease);

                        $errorStatus = false;
                        $useID = [];
                        if($request->request->has('notes') && !empty($request->request->get('notes'))){
                            foreach ($request->request->get('notes') as $note){
                                if(isset($note['text']) && !empty($note['text'])){
                                    $leaseNote = null;
                                    if(isset($note['id']) && !empty($note['id'])){
                                        $leaseNote = $em->getRepository(LeaseNotes::class)->find($note['id']);
                                    }
                                    if($leaseNote instanceof LeaseNotes){
                                        if($leaseNote->getText() != $note['text']){
                                            $leaseNote->setText($note['text']);
                                            $leaseNote->setUpdated(new \DateTime());
                                        }
                                        $useID[] = $leaseNote->getId();
                                    }
                                    else{
                                        $leaseNote = new LeaseNotes($lease, $note['text']);
                                    }
                                    $em->persist($leaseNote);
                                }
                                else{
                                    $this->addFlash('error', 'Note text is required');
                                    $errorStatus = true;
                                }
                            }
                        }
                        if($errorStatus == false){
                            $em->getRepository(LeaseNotes::class)->removeByLeaseInNot($lease, $useID);
                            $em->flush();

                            $this->addFlash('success', 'Updated!');
                        }
                    }
                    else{
                        $this->addFlash('error', 'User not found!');
                    }
                }
                else{
                    $this->addFlash('error', 'Site Status is required!');
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }

        }

        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();
        $agentType = $em->getRepository(UserType::class)->findOneBy(['name'=>'Agent']);
        if($agentType instanceof UserType){
            $users = $em->getRepository(User::class)->findBy(['type'=>$agentType]);
        }
        else{
            $users = $em->getRepository(User::class)->findAll();
        }
        $leaseNotes = $em->getRepository(LeaseNotes::class)->findBy(['lease'=>$lease]);

        $agentSaving = LeaseHelper::getAgentSaving($em, $lease);
        $agentSavingPercentage = LeaseHelper::getAgentSavingPercentage($em, $lease);
        $agentBilling = LeaseHelper::getAgentBilling($em, $lease);
        $escalationSaving = LeaseHelper::getEscalationSaving($em, $lease);
        $percentage = LeaseHelper::getLeaseRentalPercentage($em, $lease);
        $currentTotalCost = LeaseHelper::getCurrentTotalMonthlyCost($em, $lease);

        return $this->render('agent/edit.html.twig', [
            'active_menu1' => 'agents',
            'lease' => $lease,
            'leaseNotes' => $leaseNotes,
            'siteStatuses' => $siteStatuses,
            'users' => $users,
            'agentSaving' => $agentSaving,
            'agentSavingPercentage' => $agentSavingPercentage,
            'agentBilling' => $agentBilling,
            'escalationSaving' => $escalationSaving,
            'percentage' => $percentage,
            'currentTotalCost' => $currentTotalCost,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Agent'),
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", name="agent_remove", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function removeByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent', 'removable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('agent_dashboard');
        }
        $lease = $em->getRepository(Lease::class)->find($id);
        if($lease instanceof Lease){
            $lease->setAgentStatus(false);
            $lease->setAllocated(null);
            $lease->setFee(null);
            $lease->setFeeValue(null);
            $lease->setFeeDuration(null);
            $lease->setFeeEscalation(null);
            $lease->setProposedLease(null);
            $lease->setTargetRenewalRental(null);
            $lease->setTargetRenewalEscalation(null);

            $em->persist($lease);
            $em->flush();

            $this->addFlash('success', 'Site has been removed from agent!');
        }
        else{
            $this->addFlash('error', 'Site not found!');
        }

        return $this->redirectToRoute('agent_dashboard');
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/saving", name="agent_saving", methods={"GET"})
     */
    public function savingAction(Request $request, PaginatorInterface $paginator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent Saving', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        $leasesResult = $em->getRepository(Lease::class)->findLeaseByAgents(['agentStatus'=>true, 'status'=>9]);
        $avgLeaseCostBefore = 0;
        $avgEscalationBefore = 0;
        $avgLeaseCostAfter = 0;
        $avgEscalationAfter = 0;
        $avgSavingPercentage = 0;
        $countLease = 0;
        $leases = [];
        foreach ($leasesResult as $lease){
            if($lease instanceof Lease){
                $previousLeaseCost = LeaseHelper::getPreviousCurrentTotalMonthlyCost($em, $lease);
                $oldEscalation = LeaseHelper::getPreviousLeaseRentalPercentage($em, $lease);
                $newLeaseCost = LeaseHelper::getTotalMonthlyCost($em, $lease);
                $newEscalation = LeaseHelper::getLeaseRentalPercentage($em, $lease);

                $avgLeaseCostBefore += $previousLeaseCost;
                $avgEscalationBefore += $oldEscalation;
                $avgLeaseCostAfter += $newLeaseCost;
                $avgEscalationAfter += $newEscalation;
                $countLease++;

                $check = true;
                if($request->query->has('number') && !empty($request->query->get('number'))){
                    if(strpos($lease->getSite()->getNumber(), $request->query->get('number')) === false){
                        $check = false;
                    }
                }
                if($request->query->has('name') && !empty($request->query->get('name'))){
                    if(strpos($lease->getSite()->getName(), $request->query->get('name')) === false){
                        $check = false;
                    }
                }

                if($check){
                    $leases[] = [
                        'id' => $lease->getId(),
                        'site' => $lease->getSite(),
                        'previousLeaseCost' => $previousLeaseCost,
                        'oldEscalation' => $oldEscalation,
                        'proposedCost' => LeaseHelper::getProposedLeaseByLandlord($em, $lease),
                        'newLeaseCost' => $newLeaseCost,
                        'newEscalation' => $newEscalation,
                        'leaseTerm' => $lease->getTerm(),
                        'agentSaving' => LeaseHelper::getAgentSaving($em, $lease),
                        'escalationSaving' => LeaseHelper::getEscalationSaving($em, $lease),
                        'savingPercentage' => LeaseHelper::getAgentSavingPercentage($em, $lease),
                        'leaseContractualValue' => LeaseHelper::getTotalNewLeaseContractValue($em, $lease),
                        'proposedLeaseContractualValue' => LeaseHelper::getTotalProposedLeaseContractValue($em, $lease),
                        'contractualSaving' => LeaseHelper::getTotalContractValueSaving($em, $lease)
                    ];
                }
            }
        }

        if($countLease > 0){
            $avgLeaseCostBefore = round($avgLeaseCostBefore/$countLease,2);
            $avgEscalationBefore = round($avgEscalationBefore/$countLease,2);
            $avgLeaseCostAfter = round($avgLeaseCostAfter/$countLease,2);
            $avgEscalationAfter = round($avgEscalationAfter/$countLease,2);
            $avgSavingPercentage = round(abs(($avgLeaseCostBefore - $avgLeaseCostAfter) / ( ($avgLeaseCostBefore + $avgLeaseCostAfter) / 2 )) * 100, 2);
        }

        $pagination = $paginator->paginate(
            $leases,
            ($request->query->getInt('page') > 0) ? $request->query->getInt('page') : 1,
            ($request->query->getInt('limit') > 0) ? $request->query->getInt('limit') : 25
        );

        return $this->render('agent/saving.html.twig', [
            'active_menu1' => 'agents',
            'active_menu2' => 'agent_saving',
            'pagination' => $pagination,
            'avgLeaseCostBefore' => $avgLeaseCostBefore,
            'avgEscalationBefore' => $avgEscalationBefore,
            'avgLeaseCostAfter' => $avgLeaseCostAfter,
            'avgEscalationAfter' => $avgEscalationAfter,
            'avgSavingPercentage' => $avgSavingPercentage,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermissionLease' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease'),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Exception
     *
     * @Route("/saving_download", name="agent_saving_download", methods={"GET"})
     */
    public function savingDownloadAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent Saving', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Site ID')
            ->setCellValue('B1', 'Site Name')
            ->setCellValue('C1', 'Total Lease Cost (Previous)')
            ->setCellValue('D1', 'Escalation (Previous)')
            ->setCellValue('E1', "Proposed Total Lease Cost")
            ->setCellValue('F1', "Total Lease Costs(New)")
            ->setCellValue('G1', "Escalation (New)")
            ->setCellValue('H1', "New Lease Term")
            ->setCellValue('I1', "Agent Monthly Saving")
            ->setCellValue('J1', "Escalation Saving")
            ->setCellValue('K1', "Saving %")
            ->setCellValue('L1', "Total New Lease Contractual Value")
            ->setCellValue('M1', "Total Proposed Lease Contractual Value")
            ->setCellValue('N1', "Total Contractual Saving")
        ;

        $params = array_merge(['agentStatus'=>true, 'status'=>9], $request->query->all());
        $currentSymbol = $this->settingService->getCurrencySymbol();
        $leasesResult = $em->getRepository(Lease::class)->findLeaseByAgents($params);
        $i = 1;
        foreach ($leasesResult as $lease){
            if($lease instanceof Lease){
                $i++;

                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $lease->getSite()->getNumber())
                    ->setCellValue('B'.$i, $lease->getSite()->getName())
                    ->setCellValue('C'.$i, $currentSymbol." ".LeaseHelper::getPreviousCurrentTotalMonthlyCost($em, $lease))
                    ->setCellValue('D'.$i, LeaseHelper::getPreviousLeaseRentalPercentage($em, $lease))
                    ->setCellValue('E'.$i, $currentSymbol." ".LeaseHelper::getProposedLeaseByLandlord($em, $lease))
                    ->setCellValue('F'.$i, $currentSymbol." ".LeaseHelper::getTotalMonthlyCost($em, $lease))
                    ->setCellValue('G'.$i, LeaseHelper::getLeaseRentalPercentage($em, $lease))
                    ->setCellValue('H'.$i, ($lease->getTerm() == 1) ? $lease->getTerm().' month' : $lease->getTerm().' months')
                    ->setCellValue('I'.$i, $currentSymbol." ".LeaseHelper::getAgentSaving($em, $lease))
                    ->setCellValue('J'.$i, LeaseHelper::getEscalationSaving($em, $lease))
                    ->setCellValue('K'.$i, LeaseHelper::getAgentSavingPercentage($em, $lease))
                    ->setCellValue('L'.$i, $currentSymbol." ".LeaseHelper::getTotalNewLeaseContractValue($em, $lease))
                    ->setCellValue('M'.$i, $currentSymbol." ".LeaseHelper::getTotalProposedLeaseContractValue($em, $lease))
                    ->setCellValue('N'.$i, $currentSymbol." ".LeaseHelper::getTotalContractValueSaving($em, $lease))
                ;
            }
        }

        $now = new \DateTime();
        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Agent_Savings_'.$now->format('Y-m-d_H-i').'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     * @throws \Exception
     *
     * @Route("/billing", name="agent_billing", methods={"GET"})
     */
    public function billingAction(Request $request, PaginatorInterface $paginator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent Billing', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        $months = [];
        $billings = [];
        if(!empty($request->query->get('startMonth')) && !empty($request->query->get('endMonth'))){
            $leases = $em->getRepository(Lease::class)->findAllForBillingWithParams(
                $request->query->get('startMonth'),
                $request->query->get('endMonth'),
                $request->query->all()
            );
            if(!empty($leases)){
                $startMonth = new \DateTime($request->query->get('startMonth'));
                $endMonth = new \DateTime($request->query->get('endMonth'));
                $rangeDate = new \DateTime($startMonth->format('Y-m-d'));
                while($rangeDate->format('Y-m') <= $endMonth->format('Y-m') ){
                    $months[] = $rangeDate->format('F Y');
                    $rangeDate->modify('+1 month');
                }
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        $billingsMonth = [];
                        foreach ($months as $month){
                            $invoiced = false;
                            $paid = false;
                            $billing = $em->getRepository(Billing::class)->findOneBy(['month'=>$month, 'lease'=>$lease]);
                            if($billing instanceof Billing){
                                $invoiced = $billing->getInvoiced();
                                $paid = $billing->getPaid();
                            }

                            $agentBilling = 0;
                            $date = new \DateTime($month);
                            if($lease->getStartDate()->format('Y-m') <= $date->format('Y-m') && $lease->getEndDate()->format('Y-m') >= $date->format('Y-m')){
                                if($lease->getFrequencyOfLeasePayments() == 'annually'){
                                    if($lease->getStartDate()->format('m') == $date->format('m')){
                                        $agentBilling = LeaseHelper::getAgentBilling($em, $lease, $date);
                                        $agentBilling *= 12;
                                    }
                                }
                                else{
                                    $agentBilling = LeaseHelper::getAgentBilling($em, $lease, $date);
                                }
                            }
                            $billingsMonth[$month] = [
                                'agentBilling' => $agentBilling,
                                'invoiced' => $invoiced,
                                'paid' => $paid,
                            ];

                        }

                        $checkFilterInvoiced = false;
                        if($request->query->has('billingInvoicedYes') && $request->query->get('billingInvoicedYes') == 'on'
                            && $request->query->has('billingInvoicedNo') && $request->query->get('billingInvoicedNo') == 'on'
                        ){
                            $checkFilterInvoiced = true;
                        }
                        elseif ($request->query->has('billingInvoicedYes') && $request->query->get('billingInvoicedYes') == 'on'){
                            foreach ($billingsMonth as $value){
                                if($value['invoiced'] == true){
                                    $checkFilterInvoiced = true;
                                }
                            }
                        }
                        elseif ($request->query->has('billingInvoicedNo') && $request->query->get('billingInvoicedNo') == 'on'){
                            foreach ($billingsMonth as $value){
                                if($value['invoiced'] == false){
                                    $checkFilterInvoiced = true;
                                }
                            }
                        }
                        else{
                            $checkFilterInvoiced = true;
                        }

                        $checkFilterPaid = false;
                        if($request->query->has('billingPaidYes') && $request->query->get('billingPaidYes') == 'on'
                            && $request->query->has('billingPaidNo') && $request->query->get('billingPaidNo') == 'on'
                        ){
                            $checkFilterPaid = true;
                        }
                        elseif ($request->query->has('billingPaidYes') && $request->query->get('billingPaidYes') == 'on'){
                            foreach ($billingsMonth as $value){
                                if($value['paid'] == true){
                                    $checkFilterPaid = true;
                                }
                            }
                        }
                        elseif ($request->query->has('billingPaidNo') && $request->query->get('billingPaidNo') == 'on'){
                            foreach ($billingsMonth as $value){
                                if($value['paid'] == false){
                                    $checkFilterPaid = true;
                                }
                            }
                        }
                        else{
                            $checkFilterPaid = true;
                        }

                        if($checkFilterInvoiced && $checkFilterPaid){
                            $billings[] = [
                                'lease' => $lease,
                                'agentSaving' => LeaseHelper::getAgentSaving($em, $lease, $endMonth),
                                'escalation' => LeaseHelper::getLeaseRentalPercentage($em, $lease, $endMonth),
                                'billingsMonth' => $billingsMonth,
                            ];
                        }
                    }
                }
            }
        }
        $pagination = $paginator->paginate(
            $billings,
            ($request->query->getInt('page') > 0) ? $request->query->getInt('page') : 1,
            ($request->query->getInt('limit') > 0) ? $request->query->getInt('limit') : 25
        );

        return $this->render('agent/billing.html.twig', [
            'active_menu1' => 'agents',
            'active_menu2' => 'agent_billing',
            'pagination' => $pagination,
            'rangeMonths' => $months,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Agent Billing'),
            'userPermissionLease' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease'),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @Route("/billing_download", name="agent_billing_download", methods={"GET"})
     */
    public function billingDownloadAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent Billing', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Site ID')
            ->setCellValue('B1', 'Site Name')
            ->setCellValue('C1', 'Agent Fee Type(%/Amount)')
            ->setCellValue('D1', 'Agent Saving')
            ->setCellValue('E1', "Lease Start Date")
            ->setCellValue('F1', "Lease End Date")
            ->setCellValue('G1', "Lease Term")
            ->setCellValue('H1', "Lease Escalation")
            ->setCellValue('I1', "Agent Billing")
            ->setCellValue('J1', "Invoiced")
            ->setCellValue('K1', "Paid");

        if($request->query->has('month') && !empty($request->query->get('month'))){
            $leases = $em->getRepository(Lease::class)->findAllForBillingWithParams($request->query->get('month'), $request->query->all());
            if(!empty($leases)){
                $currencySymbol = $this->settingService->getCurrencySymbol();
                $date = new \DateTime($request->query->get('month'));
                $i = 1;
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        $i++;
                        if($lease->getFee() == 1){
                            $agentFeePlusValue = '% of Monthly Lease ('.$lease->getFeeValue().'%)';
                        }
                        elseif ($lease->getFee() == 2){
                            $agentFeePlusValue = '% of Saving ('.$lease->getFeeValue().'%)';
                        }
                        else{
                            $agentFeePlusValue = 'Fixed Monthly (R '.$lease->getFeeValue().')';
                        }
                        if($lease->getTerm() == 1){
                            $term = $lease->getTerm().'month';
                        }
                        else{
                            $term = $lease->getTerm().'months';
                        }
                        $invoiced = 'No';
                        $paid = 'No';
                        $billing = $em->getRepository(Billing::class)->findOneBy(['month'=>$request->query->get('month'), 'lease'=>$lease]);
                        if($billing instanceof Billing){
                            $invoiced = ($billing->getInvoiced() == true) ? 'Yes' : 'No';
                            $paid = ($billing->getPaid() == true) ? 'Yes' : 'No';
                        }


                        $spreadsheet->setActiveSheetIndex(0)
                            ->setCellValue('A'.$i, $lease->getSite()->getNumber())
                            ->setCellValue('B'.$i, $lease->getSite()->getName())
                            ->setCellValue('C'.$i, $agentFeePlusValue)
                            ->setCellValue('D'.$i, $currencySymbol." ".LeaseHelper::getAgentSaving($em, $lease, $date))
                            ->setCellValue('E'.$i, $lease->getStartDate()->format('d-m-Y'))
                            ->setCellValue('F'.$i, $lease->getEndDate()->format('d-m-Y'))
                            ->setCellValue('G'.$i, $term)
                            ->setCellValue('H'.$i, LeaseHelper::getLeaseRentalPercentage($em, $lease, $date))
                            ->setCellValue('I'.$i, $currencySymbol." ".LeaseHelper::getAgentBilling($em, $lease, $date))
                            ->setCellValue('J'.$i, $invoiced)
                            ->setCellValue('K'.$i, $paid);
                    }
                }
            }
        }

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="AgentBillingReports_'.$request->query->get("month").'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/billing/edit", name="agent_billing_edit", methods={"POST"})
     */
    public function billingEditAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent Billing', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if($request->request->has('leaseId') && !empty($request->request->get('leaseId')) && $request->request->has('month') && !empty($request->request->get('month'))){
            $lease = $em->getRepository(Lease::class)->find($request->request->get('leaseId'));
            if($lease instanceof Lease){
                $billing = $em->getRepository(Billing::class)->findOneBy(['lease'=>$lease, 'month'=>$request->request->get('month')]);
                if(!$billing instanceof Billing){
                    $billing = new Billing($request->request->get('month'), $lease);
                }

                if($request->request->has('invoiced') && !empty($request->request->get('invoiced'))){
                    if($request->request->get('invoiced') == 'true'){
                        $billing->setInvoiced(true);
                    }
                    else{
                        $billing->setInvoiced(false);
                    }
                    $em->persist($billing);
                    $em->flush();

                    return new JsonResponse(['result'=>true]);
                }

                if($request->request->has('paid') && !empty($request->request->get('paid'))){
                    if($request->request->get('paid') == 'true'){
                        $billing->setPaid(true);
                    }
                    else{
                        $billing->setPaid(false);
                    }
                    $em->persist($billing);
                    $em->flush();

                    return new JsonResponse(['result'=>true]);
                }
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/billing/selectAll", name="agent_billing_select_all", methods={"POST"})
     */
    public function billingSelectAllAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent Billing', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if(!empty($request->query->get('startMonth')) && !empty($request->query->get('endMonth'))) {
            $leases = $em->getRepository(Lease::class)->findAllForBillingWithParams(
                $request->query->get('startMonth'),
                $request->query->get('endMonth'),
                $request->query->all()
            );
            if (!empty($leases)) {
                $monthDate = new \DateTime($request->request->get('month'));
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        if($lease->getStartDate()->format('Y-m') <= $monthDate->format('Y-m') && $lease->getEndDate()->format('Y-m') >= $monthDate->format('Y-m')){
                            $billing = $em->getRepository(Billing::class)->findOneBy(['lease'=>$lease, 'month'=>$request->request->get('month')]);
                            if(!$billing instanceof Billing){
                                $billing = new Billing($request->request->get('month'), $lease);
                            }
                            if($request->request->get('field') == 'invoiced'){
                                $billing->setInvoiced(true);
                                $em->persist($billing);
                                $em->flush();
                            }
                            elseif($request->request->get('field') == 'paid'){
                                $billing->setPaid(true);
                                $em->persist($billing);
                                $em->flush();
                            }
                        }
                    }
                }
            }
        }

        return new JsonResponse(['result'=>true]);
    }

    /**
     * @return Response
     * @throws \Exception
     *
     * @Route("/user", name="agent_user", methods={"GET"})
     */
    public function userAction(){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Agent User', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $agentType = $em->getRepository(UserType::class)->findOneBy(['name'=>'Agent']);
        if($agentType instanceof UserType){
            $usersResult = $em->getRepository(User::class)->findBy(['type'=>$agentType]);
        }
        else{
            $usersResult = $em->getRepository(User::class)->findAll();
        }
        $users=[];
        if(!empty($usersResult)){
            foreach ($usersResult as $user){
                if($user instanceof User){
                    $userLeases = $em->getRepository(Lease::class)->findLeaseByAgents(['agentStatus'=>true, 'allocated'=> $user->getId()]);
                    $users[] = [
                        'id' => $user->getId(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'leases' => $userLeases,
                    ];
                }
            }
        }

        return $this->render('agent/user.html.twig', [
            'active_menu1' => 'agents',
            'active_menu2' => 'agent_user',
            'users' => $users,
            'userPermissionLease' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease')
        ]);
    }
}
