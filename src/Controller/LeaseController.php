<?php

namespace App\Controller;

use App\Entity\CPIRate;
use App\Entity\HoursOfAccess;
use App\Entity\Issue;
use App\Entity\IssueType;
use App\Entity\Landlord;
use App\Entity\LandlordContact;
use App\Entity\LandlordContactType;
use App\Entity\Lease;
use App\Entity\LeaseDepositType;
use App\Entity\LeaseDocument;
use App\Entity\LeaseDocumentType;
use App\Entity\LeaseElectricityType;
use App\Entity\LeaseNotes;
use App\Entity\LeaseOtherUtilityCostCategory;
use App\Entity\LeaseRentalCost;
use App\Entity\LeaseType;
use App\Entity\ManagementStatus;
use App\Entity\RentalCostCategory;
use App\Entity\Site;
use App\Entity\LandlordDocumentStatus;
use App\Entity\SiteInventory;
use App\Entity\SiteInventoryCategory;
use App\Entity\User;
use App\Entity\UserType;
use App\Helper\LeaseHelper;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class LeasesController
 * @package App\Controller
 *
 * @Route("/lease")
 */
class LeaseController extends AbstractController
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
     * @Route("/", name="lease_list")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $leasesResult = $em->getRepository(Lease::class)->findAllWithParams(['endDate'=>true]);
        $leases = [];
        foreach ($leasesResult as $lease){
            if($lease instanceof Lease){
                $issueStatus = "No";
                $siteIssue = $em->getRepository(Issue::class)->count(['site'=>$lease->getSite(), 'status'=>true]);
                if($siteIssue > 0){
                    $issueStatus = 'Yes';
                }

                $leases[] = [
                    'id' => $lease->getId(),
                    'site' => $lease->getSite(),
                    'startDate' => $lease->getStartDate(),
                    'endDate' => $lease->getEndDate(),
                    'totalCost' => LeaseHelper::getTotalMonthlyCost($em, $lease),
                    'currentTotalCost' => LeaseHelper::getCurrentTotalMonthlyCost($em, $lease),
                    'issueStatus' => $issueStatus,
                ];
            }
        }

        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();

        return $this->render('sites_and_leases/leases/index.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'leases',
            'leases' => $leases,
            'siteStatuses' => $siteStatuses,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease')
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/add", name="lease_add", methods={"GET", "POST"})
     */
    public function addAction(Request $request, ValidatorInterface $validator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'added')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $lease = new Lease();
        $rentalCosts = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$lease]);
        $leaseDocuments = $em->getRepository(LeaseDocument::class)->findBy(['lease'=>$lease]);
        $leaseNotes = $em->getRepository(LeaseNotes::class)->findBy(['lease'=>$lease]);
        $agentData = [];

        if($request->request->has('submit1') && $request->request->get('submit1') == 'send'){
            $errorStatus = false;
            if($request->request->has('agentData')){
                $agentData = $request->request->get('agentData');
            }
            //Update Lease Field
            $lease->update($request->request->get('lease'));
            if(!$lease->getLandlord() instanceof Landlord){
                $landlord = $em->getRepository(Landlord::class)->find($lease->getLandlord());
                if($landlord instanceof Landlord){
                    $lease->setLandlord($landlord);
                }
            }
            if(!$lease->getSite() instanceof Site){
                $site = $em->getRepository(Site::class)->find($lease->getSite());
                if($site instanceof Site){
                    $lease->setSite($site);
                }
            }
            if(!$lease->getElectricityType() instanceof LeaseElectricityType){
                $electricityType = $em->getRepository(LeaseElectricityType::class)->find($lease->getElectricityType());
                if($electricityType instanceof LeaseElectricityType){
                    $lease->setElectricityType($electricityType);
                }
            }
            if($lease->getDepositStatus() == true && !$lease->getDepositType() instanceof LeaseDepositType){
                $depositType = $em->getRepository(LeaseDepositType::class)->find($lease->getDepositType());
                if($depositType instanceof LeaseDepositType){
                    $lease->setDepositType($depositType);
                }
            }
            if(!$lease->getDocumentStatus() instanceof LandlordDocumentStatus){
                $documentStatus = $em->getRepository(LandlordDocumentStatus::class)->find($lease->getDocumentStatus());
                if($documentStatus instanceof LandlordDocumentStatus){
                    $lease->setDocumentStatus($documentStatus);
                }
            }
            if(!empty($lease->getAllocated()) && !$lease->getAllocated() instanceof User){
                $allocatedUser = $em->getRepository(User::class)->find($lease->getAllocated());
                if($allocatedUser instanceof User){
                    $lease->setAllocated($allocatedUser);
                }
            }
            $leaseTypes = new ArrayCollection();
            if(!empty($lease->getType())){
                foreach ($lease->getType() as $leaseType){
                    if(!$leaseType instanceof LeaseType){
                        $leaseType = $em->getRepository(LeaseType::class)->find($leaseType);
                    }

                    if($leaseType instanceof LeaseType){
                        $leaseTypes->add($leaseType);
                    }
                }
            }
            $lease->setType($leaseTypes);
            $otherUtilityCosts = new ArrayCollection();
            if(!empty($lease->getOtherUtilityCost())){
                foreach ($lease->getOtherUtilityCost() as $otherUtilityCost){
                    if(!$otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                        $otherUtilityCost = $em->getRepository(LeaseOtherUtilityCostCategory::class)->find($otherUtilityCost);
                    }
                    if($otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                        $otherUtilityCosts->add($otherUtilityCost);
                    }
                }
            }
            $lease->setOtherUtilityCost($otherUtilityCosts);

            //Validation landlord
            $errors = $validator->validate($lease);
            if(count($errors) === 0){
                $em->persist($lease);
            }
            else {
                $errorStatus = true;
                foreach ($errors as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }

            //Update RentalCosts
            $rentalCosts = [];
            if($request->request->has('leaseRentalCost') && !empty($request->request->get('leaseRentalCost'))){
                foreach ($request->request->get('leaseRentalCost') as $rentalCostItem){
                    $rentalCostCategory = $em->getRepository(RentalCostCategory::class)->find($rentalCostItem['category']);
                    if($rentalCostCategory instanceof RentalCostCategory){
                        if(isset($rentalCostItem['amount']) && !empty($rentalCostItem['amount'])){
                            $startDate = new \DateTime($rentalCostItem['startDate']);
                            $rentalCost = new LeaseRentalCost($lease, $rentalCostCategory, $rentalCostItem['amount'], $startDate);
                            $em->persist($rentalCost);

                            $rentalCosts[] = $rentalCost;
                        }
                        else{
                            $errorStatus = true;
                            $this->addFlash('error', 'Monthly Rental Cost Amount is required');
                        }
                    }
                    else{
                        $errorStatus = true;
                        $this->addFlash('error', 'Monthly Rental Cost Category is required');
                    }
                }
            }

            //Update Documents
            $leaseDocuments = [];
            if($request->request->has('document') && !empty($request->request->get('document'))){
                $documentFiles = $request->files->get('document');
                foreach ($request->request->get('document') as $key => $documentItem){
                    $leaseDocumentType = $em->getRepository(LeaseDocumentType::class)->find($documentItem['type']);
                    if($leaseDocumentType instanceof LeaseDocumentType){
                        $leaseDocument = new LeaseDocument($lease, $leaseDocumentType, null, null);
                        $filePath = null;
                        $originalName = null;
                        if(isset($documentFiles[$key]['document']) && $documentFiles[$key]['document'] instanceof UploadedFile) {
                            $fileUpload = $documentFiles[$key]['document'];
                            $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                            $originalName =$fileUpload->getClientOriginalName();
                            $path = "upload/lease/documents";
                            $filePath = $request->getSchemeAndHttpHost()."/".$path."/".$fileName;
                            try {
                                $fileUpload->move($path, $fileName);
                            } catch (\Exception $e) {
                                $this->addFlash('error', $e->getMessage());
                                $errorStatus = true;
                            }
                        }
                        elseif(isset($documentItem['document']) && !empty($documentItem['document'])){
                            $filePath = $documentItem['document'];
                            if(isset($documentItem['name']) && !empty($documentItem['name'])){
                                $originalName = $documentItem['name'];
                            }
                        }

                        if(!empty($filePath)){
                            $leaseDocument->setDocument($filePath);
                            if(!empty($originalName)){
                                $leaseDocument->setName($originalName);
                            }
                            $em->persist($leaseDocument);
                        }
                        else{
                            $this->addFlash('error', 'Document file is required');
                            $errorStatus = true;
                        }
                        $leaseDocuments[] = $leaseDocument;
                    }
                    else{
                        $this->addFlash('error', 'Document Type is required');
                        $errorStatus = true;
                    }
                }
            }

            //Update NOTES
            $leaseNotes = [];
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
                        }
                        else{
                            $leaseNote = new LeaseNotes($lease, $note['text']);
                        }
                        $em->persist($leaseNote);

                        $leaseNotes[] = $leaseNote;
                    }
                    else{
                        $this->addFlash('error', 'Note text is required');
                        $errorStatus = true;
                    }
                }
            }

            if($errorStatus == false){
                if($lease->getAgentStatus() == false){
                    $lease->setAllocated(null);
                    $lease->setFee(null);
                    $lease->setFeeValue(null);
                    $lease->setFeeDuration(null);
                    $lease->setFeeEscalation(null);
                    $lease->setProposedLease(null);
                    $lease->setTargetRenewalRental(null);
                    $lease->setTargetRenewalEscalation(null);
                }
                else{
                    if($request->request->has('feeDurationCheckbox') && $request->request->get('feeDurationCheckbox') == 'all'){
                        $lease->setFeeDuration('all');
                    }
                }

                if($request->request->has('previousDate') && !empty($request->request->get('previousDate'))){
                    $oldLease = $em->getRepository(Lease::class)->findOneBy(['site'=>$lease->getSite(), 'renewed'=>false]);
                    $previousDate = new \DateTime($request->request->get('previousDate'));
                    if($oldLease instanceof Lease && $previousDate->format('Y-m-d') <= $oldLease->getEndDate()->format('Y-m-d')){
                        $oldLease->setEndDate($previousDate);
                        $oldLease->setRenewed(true);
                        $oldLease->setUpdated(new \DateTime());
                        $em->persist($oldLease);
                    }
                }

                $em->flush();


                $this->addFlash('success', 'Lease has been added!');
                return $this->redirectToRoute('lease_list');
            }

        }

        $landlords = $em->getRepository(Landlord::class)->findAll();
        $sites = $em->getRepository(Site::class)->findAll();
        $leaseTypes = $em->getRepository(LeaseType::class)->findAll();
        $rentalCostCategories = $em->getRepository(RentalCostCategory::class)->findAll();
        $electricityTypes = $em->getRepository(LeaseElectricityType::class)->findAll();
        $otherUtilityCostCategories = $em->getRepository(LeaseOtherUtilityCostCategory::class)->findAll();
        $depositTypes = $em->getRepository(LeaseDepositType::class)->findAll();
        $documentsTypes = $em->getRepository(LeaseDocumentType::class)->findAll();
        $documentsStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();
        $agentType = $em->getRepository(UserType::class)->findOneBy(['name'=>'Agent']);
        if($agentType instanceof UserType){
            $users = $em->getRepository(User::class)->findBy(['type'=>$agentType]);
        }
        else{
            $users = $em->getRepository(User::class)->findAll();
        }

        $now = new \DateTime();
        $percentage = 0;
        $cpiRate = $em->getRepository(CPIRate::class)->findOneBy(['month'=>$now->format('F Y')]);
        if($cpiRate instanceof CPIRate){
            $percentage = $cpiRate->getValue();
        }

        return $this->render('sites_and_leases/leases/add.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'leases',
            'landlords' => $landlords,
            'sites' => $sites,
            'leaseTypes' => $leaseTypes,
            'rentalCostCategories' => $rentalCostCategories,
            'electricityTypes' => $electricityTypes,
            'otherUtilityCostCategories' => $otherUtilityCostCategories,
            'depositTypes' => $depositTypes,
            'documentsTypes' => $documentsTypes,
            'documentsStatuses' => $documentsStatuses,
            'lease' => $lease,
            'rentalCosts' => $rentalCosts,
            'leaseDocuments' => $leaseDocuments,
            'leaseNotes' => $leaseNotes,
            'users' => $users,
            'agentData' => $agentData,
            'percentage' => floatval($percentage),
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/edit/{id}", name="lease_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function editByIdAction(Request $request, ValidatorInterface $validator, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'viewable') && !UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $lease = $em->getRepository(Lease::class)->find($id);
        if(!$lease instanceof Lease){
            $this->addFlash('error', 'Lease not found');

            return $this->redirectToRoute('lease_list');
        }

        // STEP 1
        if($request->request->has('submit_step_1')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
                $siteData = $request->request->get('site');
                if(isset($siteData['siteStatus']) && !empty($siteData['siteStatus'])){
                    $siteStatus = $em->getRepository(ManagementStatus::class)->find($siteData['siteStatus']);
                    if($siteStatus instanceof ManagementStatus){
                        $lease->getSite()->setSiteStatus($siteStatus);
                    }
                }
                $leaseData = $request->request->get('lease');
                $lease->update($leaseData);
                if(array_key_exists('renewalStatus', $leaseData)){
                    $lease->setRenewalStatus(true);
                }
                else{
                    $lease->setRenewalStatus(false);
                }
                if(array_key_exists('terminationClauseStatus', $leaseData)){
                    $lease->setTerminationClauseStatus(true);
                }
                else{
                    $lease->setTerminationClauseStatus(false);
                }
                if(!$lease->getLandlord() instanceof Landlord){
                    $landlord = $em->getRepository(Landlord::class)->find($lease->getLandlord());
                    if($landlord instanceof Landlord){
                        $lease->setLandlord($landlord);
                    }
                }
                $leaseTypes = new ArrayCollection();
                if(!empty($lease->getType())){
                    foreach ($lease->getType() as $leaseType){
                        if(!$leaseType instanceof LeaseType){
                            $leaseType = $em->getRepository(LeaseType::class)->find($leaseType);
                        }

                        if($leaseType instanceof LeaseType){
                            $leaseTypes->add($leaseType);
                        }
                    }
                }
                $lease->setType($leaseTypes);

                $errors = $validator->validate($lease);
                if(count($errors) === 0){
                    $em->persist($lease);
                    $errorStatus = false;
                    $useID = [];
                    if ($request->request->has('notes') && !empty($request->request->get('notes'))) {
                        foreach ($request->request->get('notes') as $note) {
                            if (isset($note['text']) && !empty($note['text'])) {
                                $leaseNote = null;
                                if (isset($note['id']) && !empty($note['id'])) {
                                    $leaseNote = $em->getRepository(LeaseNotes::class)->find($note['id']);
                                }
                                if ($leaseNote instanceof LeaseNotes) {
                                    if ($leaseNote->getText() != $note['text']) {
                                        $leaseNote->setText($note['text']);
                                        $leaseNote->setUpdated(new \DateTime());
                                    }
                                    $useID[] = $leaseNote->getId();
                                } else {
                                    $leaseNote = new LeaseNotes($lease, $note['text']);
                                }
                                $em->persist($leaseNote);

                                $leaseNotes[] = $leaseNote;
                            } else {
                                $this->addFlash('error', 'Note text is required');
                                $errorStatus = true;
                            }
                        }
                    }
                    if ($errorStatus == false) {
                        $em->getRepository(LeaseNotes::class)->removeByLeaseInNot($lease, $useID);

                        $em->flush();
                        $this->addFlash('success', 'Lease has been update!');
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
        //STEP 2
        if($request->request->has('submit_step_2')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
                $leaseData = $request->request->get('lease');
                $lease->update($leaseData);
                if(!$lease->getElectricityType() instanceof LeaseElectricityType){
                    $electricityType = $em->getRepository(LeaseElectricityType::class)->find($lease->getElectricityType());
                    if($electricityType instanceof LeaseElectricityType){
                        $lease->setElectricityType($electricityType);
                    }
                }
                $otherUtilityCosts = new ArrayCollection();
                if(isset($leaseData['otherUtilityCost']) && !empty($leaseData['otherUtilityCost'])){
                    if(!empty($lease->getOtherUtilityCost())){
                        foreach ($lease->getOtherUtilityCost() as $otherUtilityCost){
                            if(!$otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                                $otherUtilityCost = $em->getRepository(LeaseOtherUtilityCostCategory::class)->find($otherUtilityCost);
                            }
                            if($otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                                $otherUtilityCosts->add($otherUtilityCost);
                            }
                        }
                    }
                }
                $lease->setOtherUtilityCost($otherUtilityCosts);

                if (array_key_exists('depositStatus', $leaseData)) {
                    $lease->setDepositStatus(true);
                } else {
                    $lease->setDepositStatus(false);
                }
                if ($lease->getDepositStatus() == true) {
                    if (!$lease->getDepositType() instanceof LeaseDepositType) {
                        $depositType = $em->getRepository(LeaseDepositType::class)->find($lease->getDepositType());
                        if ($depositType instanceof LeaseDepositType) {
                            $lease->setDepositType($depositType);
                        }
                    }
                } else {
                    $lease->setDepositType(null);
                    $lease->setDepositAmount(null);
                }

                $errors = $validator->validate($lease);
                if(count($errors) === 0){
                    $em->persist($lease);
                    $em->flush();

                    $this->addFlash('success', 'Lease has been update!');

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

        //STEP 4
        if($request->request->has('submit_step_4')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
                $documentStatusOld = $lease->getDocumentStatus();
                $lease->update($request->request->get('lease'));
                if (!$lease->getDocumentStatus() instanceof LandlordDocumentStatus) {
                    $documentStatus = $em->getRepository(LandlordDocumentStatus::class)->find(
                        $lease->getDocumentStatus()
                    );
                    if ($documentStatus instanceof LandlordDocumentStatus) {
                        $lease->setDocumentStatus($documentStatus);
                    }
                }
                if($documentStatusOld instanceof LandlordDocumentStatus && $lease->getDocumentStatus() instanceof LandlordDocumentStatus){
                    if($documentStatusOld->getId() != $lease->getDocumentStatus()->getId() || empty($lease->getDocumentStatusUpdated())){
                        $lease->setDocumentStatusUpdated(new \DateTime());
                    }
                }
                else{
                    $lease->setDocumentStatusUpdated(new \DateTime());
                }

                $errors = $validator->validate($lease);
                if (count($errors) === 0) {
                    $em->persist($lease);

                    $errorStatus = false;
                    $useID = [];
                    if ($request->request->has('document') && !empty(
                        $request->request->get(
                            'document'
                        )
                        ) && !empty($request->files->get('document'))) {
                        $documentFiles = $request->files->get('document');
                        foreach ($request->request->get('document') as $key => $documentItem) {
                            $leaseDocumentType = $em->getRepository(LeaseDocumentType::class)->find(
                                $documentItem['type']
                            );
                            if ($leaseDocumentType instanceof LeaseDocumentType) {
                                $leaseDocument = null;
                                if (isset($documentItem['id']) && !empty($documentItem['id'])) {
                                    $leaseDocument = $em->getRepository(LeaseDocument::class)->findOneBy(
                                        ['lease' => $lease, 'id' => $documentItem['id']]
                                    );
                                }
                                if ($leaseDocument instanceof LeaseDocument) {
                                    $useID[] = $leaseDocument->getId();
                                    $leaseDocument->setType($leaseDocumentType);
                                } else {
                                    $leaseDocument = new LeaseDocument($lease, $leaseDocumentType, null, null);
                                }
                                $filePath = null;
                                $originalName = null;
                                if (isset($documentFiles[$key]['document']) && $documentFiles[$key]['document'] instanceof UploadedFile) {
                                    $fileUpload = $documentFiles[$key]['document'];
                                    $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                                    $originalName = $fileUpload->getClientOriginalName();
                                    $path = "upload/lease/documents";
                                    $filePath = $request->getSchemeAndHttpHost()."/".$path."/".$fileName;
                                    try {
                                        $fileUpload->move($path, $fileName);
                                    } catch (\Exception $e) {
                                        $this->addFlash('error', $e->getMessage());
                                        $errorStatus = true;
                                    }
                                } elseif (isset($documentItem['document']) && !empty($documentItem['document'])) {
                                    $filePath = $documentItem['document'];
                                    if (isset($documentItem['name']) && !empty($documentItem['name'])) {
                                        $originalName = $documentItem['name'];
                                    }
                                }

                                if (!empty($filePath)) {
                                    $leaseDocument->setDocument($filePath);
                                    if (!empty($originalName)) {
                                        $leaseDocument->setName($originalName);
                                    }
                                    $em->persist($leaseDocument);
                                } else {
                                    $this->addFlash('error', 'Document file is required');
                                    $errorStatus = true;
                                }
                                $leaseDocuments[] = $leaseDocument;
                            } else {
                                $this->addFlash('error', 'Document Type is required');
                                $errorStatus = true;
                            }
                        }
                    }

                    if ($errorStatus == false) {
                        $em->getRepository(LeaseDocument::class)->removeByLeaseInNot($lease, $useID);

                        $em->flush();
                        $this->addFlash('success', 'Lease has been update!');
                    }
                } else {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }
        //STEP 6 INVENTORY
        if($request->request->has('submit_step_6_inventory')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
                $errorStatus = false;
                $siteInventoryIds = [];
                if ($request->request->has('siteInventory') && !empty($request->request->get('siteInventory'))) {
                    foreach ($request->request->get('siteInventory') as $siteInventoryItem) {
                        $siteInventory = null;
                        $updateStatus = false;
                        if (isset($siteInventoryItem['id']) && !empty($siteInventoryItem['id'])) {
                            $siteInventory = $em->getRepository(SiteInventory::class)->findOneBy(['site' => $lease->getSite(), 'id' => $siteInventoryItem['id']]);
                        }
                        if ($siteInventory instanceof SiteInventory) {
                            if (isset($siteInventoryItem['category']) && $siteInventory->getCategory()->getId() != $siteInventoryItem['category']) {
                                $updateStatus = true;
                            }
                            if (isset($siteInventoryItem['size']) && $siteInventory->getSize() != $siteInventoryItem['size']) {
                                $updateStatus = true;
                            }
                            if (isset($siteInventoryItem['height']) && $siteInventory->getHeight() != $siteInventoryItem['height']) {
                                $updateStatus = true;
                            }
                            if (isset($siteInventoryItem['weight']) && $siteInventory->getWeight() != $siteInventoryItem['weight']) {
                                $updateStatus = true;
                            }
                            if (isset($siteInventoryItem['quantity']) && $siteInventory->getQuantity() != $siteInventoryItem['quantity']) {
                                $updateStatus = true;
                            }
                            if (isset($siteInventoryItem['info']) && $siteInventory->getInfo() != $siteInventoryItem['info']) {
                                $updateStatus = true;
                            }
                            $siteInventory->update($siteInventoryItem);
                            $siteInventoryIds[] = $siteInventory->getId();
                        } else {
                            $siteInventory = new SiteInventory($lease->getSite(), $siteInventoryItem);
                        }
                        if (!$siteInventory->getCategory() instanceof SiteInventoryCategory && !empty($siteInventory->getCategory())) {
                            $siteInventoryCategory = $em->getRepository(SiteInventoryCategory::class)->find($siteInventory->getCategory());
                            if ($siteInventoryCategory instanceof SiteInventoryCategory) {
                                $siteInventory->setCategory($siteInventoryCategory);
                            }
                        }
                        $errors = $validator->validate($siteInventory);
                        if (count($errors) === 0) {
                            if ($updateStatus) {
                                $siteInventory->setUpdated(new \DateTime());
                            }
                            $em->persist($siteInventory);
                        } else {
                            $errorStatus = true;
                            foreach ($errors as $error) {
                                $this->addFlash('error', $error->getMessage());
                            }
                        }
                    }
                }

                if ($errorStatus == false) {
                    $em->getRepository(SiteInventory::class)->removeBySiteNotIn($lease->getSite(), $siteInventoryIds);

                    $em->flush();
                    $this->addFlash('success', 'Site Inventories has been updated');
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }
        //STEP 6 EMERGENCY ACCESS
        if($request->request->has('submit_step_6_emergency_access')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {

                $siteData = $request->request->get('site');
                if (
                    isset($siteData['hoursOfAccess']) && !empty($siteData['hoursOfAccess'])
                    && isset($siteData['primaryEmergencyContact']) && !empty($siteData['primaryEmergencyContact'])
                ) {
                    $hoursOfAccessType = $em->getRepository(HoursOfAccess::class)->find($siteData['hoursOfAccess']);
                    $primaryEmergencyContact = $em->getRepository(LandlordContact::class)->find($siteData['primaryEmergencyContact']);
                    if ($hoursOfAccessType instanceof HoursOfAccess && $primaryEmergencyContact instanceof LandlordContact) {
                        $lease->getSite()->setHoursOfAccess($hoursOfAccessType);
                        $lease->getSite()->setOtherAccess(isset($siteData['otherAccess']) ? $siteData['otherAccess'] : null);
                        $lease->getSite()->setPrimaryEmergencyContact($primaryEmergencyContact);
                        if(isset($siteData['secondaryEmergencyContact']) && !empty($siteData['secondaryEmergencyContact'])){
                            $secondaryEmergencyContact = $em->getRepository(LandlordContact::class)->find($siteData['secondaryEmergencyContact']);
                            if($secondaryEmergencyContact instanceof LandlordContact){
                                $lease->getSite()->setSecondaryEmergencyContact($secondaryEmergencyContact);
                            }
                            else{
                                $lease->getSite()->setSecondaryEmergencyContact(null);
                            }
                        }
                        else{
                            $lease->getSite()->setSecondaryEmergencyContact(null);
                        }
                        if($request->request->has('emergencyAccessUpdatedManually') && !empty($request->request->get('emergencyAccessUpdatedManually'))){
                            $lease->getSite()->setEmergencyAccessUpdated(new \DateTime($siteData['emergencyAccessUpdated']));
                        }
                        else{
                            $lease->getSite()->setEmergencyAccessUpdated(new \DateTime());
                        }

                        $em->persist($lease->getSite());
                        $em->flush();

                        $this->addFlash('success', 'Emergency access details has been update!');
                    } else {
                        if (!$hoursOfAccessType instanceof HoursOfAccess) {
                            $this->addFlash('error', 'Hours of access is required!');
                        }
                        if(!$primaryEmergencyContact instanceof LandlordContact){
                            $this->addFlash('error', 'Primary Emergency Contact is required!');
                        }
                    }
                } else {
                    if (!isset($siteData['hoursOfAccess']) || empty($siteData['hoursOfAccess'])) {
                        $this->addFlash('error', 'Hours of access is required!');
                    }
                    if(!isset($siteData['primaryEmergencyContact']) || empty($siteData['primaryEmergencyContact'])){
                        $this->addFlash('error', 'Primary Emergency Contact is required!');
                    }
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }

        $landlords = $em->getRepository(Landlord::class)->findAll();
        $leaseTypes = $em->getRepository(LeaseType::class)->findAll();
        $rentalCostCategories = $em->getRepository(RentalCostCategory::class)->findAll();
        $electricityTypes = $em->getRepository(LeaseElectricityType::class)->findAll();
        $otherUtilityCostCategories = $em->getRepository(LeaseOtherUtilityCostCategory::class)->findAll();
        $depositTypes = $em->getRepository(LeaseDepositType::class)->findAll();
        $documentsTypes = $em->getRepository(LeaseDocumentType::class)->findAll();
        $documentsStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();
        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();
        $percentage = LeaseHelper::getLeaseRentalPercentage($em, $lease);
        $leaseRentalCostsResult = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$lease]);
        $leaseRentalCosts= [];
        foreach ($leaseRentalCostsResult as $leaseRentalCost){
            if($leaseRentalCost instanceof LeaseRentalCost){
                $currentAmount = LeaseHelper::getRentalCostAmountWithEscalation($lease, $leaseRentalCost, $percentage);
                $leaseRentalCosts[] = [
                    'id' => $leaseRentalCost->getId(),
                    'category' => $leaseRentalCost->getCategory(),
                    'amount' => $leaseRentalCost->getAmount(),
                    'currentAmount' => $currentAmount,
                    'startDate' => $leaseRentalCost->getStartDate(),
                    'additional' => $leaseRentalCost->getAdditional()
                ];
            }
        }

        $leaseDocuments = $em->getRepository(LeaseDocument::class)->findBy(['lease'=>$lease]);
        $leaseNotes = $em->getRepository(LeaseNotes::class)->findBy(['lease'=>$lease]);

        $issueTypes = $em->getRepository(IssueType::class)->findAll();
        $openIssues = $em->getRepository(Issue::class)->findBy(['site'=>$lease->getSite(), 'status'=>true], ['logged'=>'DESC']);
        $closeIssues = $em->getRepository(Issue::class)->findBy(['site'=>$lease->getSite(), 'status'=>false], ['logged'=>'DESC']);

        $siteInventoryCategories = $em->getRepository(SiteInventoryCategory::class)->findAll();
        $siteInventories = $em->getRepository(SiteInventory::class)->findBy(['site'=>$lease->getSite()]);

        $landlordContacts = $em->getRepository(LandlordContact::class)->findAll();
        $landlordContactTypes = $em->getRepository(LandlordContactType::class)->findAll();

        $previousLeases = LeaseHelper::getPreviousLeaseWithRentalRange($em, $lease);

        $hoursOfAccessTypes = $em->getRepository(HoursOfAccess::class)->findAll();

        return $this->render('sites_and_leases/leases/edit.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'leases',
            'lease' => $lease,
            'landlords' => $landlords,
            'leaseTypes' => $leaseTypes,
            'rentalCostCategories' => $rentalCostCategories,
            'electricityTypes' => $electricityTypes,
            'otherUtilityCostCategories' => $otherUtilityCostCategories,
            'depositTypes' => $depositTypes,
            'documentsTypes' => $documentsTypes,
            'documentsStatuses' => $documentsStatuses,
            'siteStatuses' => $siteStatuses,
            'leaseRentalCosts' => $leaseRentalCosts,
            'leaseDocuments' => $leaseDocuments,
            'leaseNotes' => $leaseNotes,
            'percentage' => $percentage,
            'issueTypes' => $issueTypes,
            'openIssues' => $openIssues,
            'closeIssues' => $closeIssues,
            'siteInventoryCategories' => $siteInventoryCategories,
            'siteInventories' => $siteInventories,
            'landlordContacts' => $landlordContacts,
            'landlordContactTypes' => $landlordContactTypes,
            'previousLeases' => $previousLeases,
            'hoursOfAccessTypes' => $hoursOfAccessTypes,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease'),
            'userPermissionSite' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Site'),
            'userPermissionIssue' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Issue'),
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/view/{id}", name="lease_view", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function viewByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $lease = $em->getRepository(Lease::class)->find($id);
        if(!$lease instanceof Lease){
            $this->addFlash('error', 'Lease not found');

            return $this->redirectToRoute('lease_list');
        }


        $percentage = LeaseHelper::getLeaseRentalPercentage($em, $lease);
        $leaseRentalCostsResult = $em->getRepository(LeaseRentalCost::class)->findBy(['lease'=>$lease]);
        $leaseRentalCosts= [];
        foreach ($leaseRentalCostsResult as $leaseRentalCost){
            if($leaseRentalCost instanceof LeaseRentalCost){
                $currentAmount = LeaseHelper::getRentalCostAmountWithEscalation($lease, $leaseRentalCost, $percentage);
                $leaseRentalCosts[] = [
                    'id' => $leaseRentalCost->getId(),
                    'category' => $leaseRentalCost->getCategory(),
                    'amount' => $leaseRentalCost->getAmount(),
                    'currentAmount' => $currentAmount,
                    'startDate' => $leaseRentalCost->getStartDate(),
                    'additional' => $leaseRentalCost->getAdditional()
                ];
            }
        }
        $leaseDocuments = $em->getRepository(LeaseDocument::class)->findBy(['lease'=>$lease]);
        $leaseNotes = $em->getRepository(LeaseNotes::class)->findBy(['lease'=>$lease]);

        $issueTypes = $em->getRepository(IssueType::class)->findAll();
        $issues = $em->getRepository(Issue::class)->findBy(['site'=>$lease->getSite()], ['logged'=>'DESC']);

        $siteInventories = $em->getRepository(SiteInventory::class)->findBy(['site'=>$lease->getSite()]);

        $previousLeases = LeaseHelper::getPreviousLeaseWithRentalRange($em, $lease);

        $hoursOfAccessTypes = $em->getRepository(HoursOfAccess::class)->findAll();

        return $this->render('sites_and_leases/leases/view.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'leases',
            'lease' => $lease,
            'leaseRentalCosts' => $leaseRentalCosts,
            'leaseDocuments' => $leaseDocuments,
            'leaseNotes' => $leaseNotes,
            'percentage' => $percentage,
            'issueTypes' => $issueTypes,
            'issues' => $issues,
            'siteInventories' => $siteInventories,
            'previousLeases' => $previousLeases,
            'hoursOfAccessTypes' => $hoursOfAccessTypes,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease'),
            'userPermissionSite' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Site'),
            'userPermissionIssue' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Issue'),
            'userPermissionLandlord' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Landlord'),
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", name="lease_remove", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function removeByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'removable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('lease_list');
        }
        $lease = $em->getRepository(Lease::class)->find($id);
        if($lease instanceof Lease){
            $oldLease = $em->getRepository(Lease::class)->findOneBy(['site'=>$lease->getSite(), 'renewed'=>true], ['endDate'=>'DESC']);
            if($oldLease instanceof Lease){
                $oldLease->setRenewed(false);
                $em->persist($oldLease);
            }
            $em->remove($lease);
            $em->flush();

            $this->addFlash('success', 'Lease has been removed');
        }
        else{
            $this->addFlash('error', 'Lease not found');
        }
        return $this->redirectToRoute('lease_list');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/checkLease", name="lease_check_lease", methods={"POST"})
     */
    public function checkLeaseAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'added')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        if($request->request->has('siteId') && !empty($request->request->get('siteId'))
            && $request->request->has('startDate') && !empty($request->request->get('startDate'))
        ){
            $startDate = new \DateTime($request->request->get('startDate'));
            $site = $em->getRepository(Site::class)->find($request->request->get('siteId'));
            if($site instanceof Site){
                $previousDate = $startDate->modify('-1 days');
                $lease = $em->getRepository(Lease::class)->findOneBy(['site'=>$site, 'renewed'=>false]);
                if($lease instanceof Lease && $startDate->format('Y-m-d') < $lease->getEndDate()->format('Y-m-d')){

                    return new JsonResponse(['result'=>true, 'previousDate'=>$previousDate->format('d M y')]);
                }
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/createRentalCategory", name="rental_category_create", methods={"POST"})
     */
    public function createRentalCategoryAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('action')){
            if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', $request->request->get('action'))) {
                return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
            }
        }

        if($request->request->has('name') && !empty($request->request->get('name'))){
            $rentalCategory = $em->getRepository(RentalCostCategory::class)->findOneBy(['name'=>$request->request->get('name')]);
            if(!$rentalCategory instanceof RentalCostCategory){
                $rentalCategory = new RentalCostCategory($request->request->get('name'));
                $em->persist($rentalCategory);
                $em->flush();
            }

            return new JsonResponse(['result'=>true, 'id'=>$rentalCategory->getId(), 'name'=>$rentalCategory->getName()]);
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/{id}/addAddendum", name="lease_add_addendum", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function addAddendumByLeaseIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        $lease = $em->getRepository(Lease::class)->find($id);
        if($lease instanceof Lease){
            if($request->request->has('category') && !empty($request->request->get('category'))
                && $request->request->has('amount') && !empty($request->request->get('amount'))
                && $request->request->has('startDate') && !empty($request->request->get('startDate'))
            ){
                $category = $em->getRepository(RentalCostCategory::class)->find($request->request->get('category'));
                if($category instanceof RentalCostCategory){
                    $startDate = new \DateTime($request->request->get('startDate'));
                    $rentalCost = new LeaseRentalCost($lease, $category, $request->request->get('amount'), $startDate, true);
                    $em->persist($rentalCost);
                    $em->flush();

//                    $percentage = LeaseHelper::getLeaseRentalPercentage($em, $lease);
                    $currentAmount = $rentalCost->getAmount();
                    $now = new \DateTime();
                    $totalStatus = false;
                    if($rentalCost->getStartDate()->format('Y-m-d') <= $now->format('Y-m-d')){
//                        $currentAmount += round((($rentalCost->getAmount() * $percentage) / 100), 2);
                        $totalStatus = true;
                    }

                    return new JsonResponse([
                        'result'=>true,
                        'id'=>$rentalCost->getId(),
                        'category'=>[
                            'id'=>$rentalCost->getCategory()->getId(),
                            'name'=>$rentalCost->getCategory()->getName()
                        ],
                        'amount'=>$rentalCost->getAmount(),
                        'currentAmount'=>$currentAmount,
                        'startDate'=>$rentalCost->getStartDate()->format('d M Y'),
                        'totalStatus'=>$totalStatus
                    ]);
                }
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/addendum/edit/{id}", name="lease_edit_addendum", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function editAddendumByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        $rentalCost = $em->getRepository(LeaseRentalCost::class)->find($id);
        if($rentalCost instanceof LeaseRentalCost){
            if($request->request->has('category') && !empty($request->request->get('category'))
                && $request->request->has('amount') && !empty($request->request->get('amount'))
                && $request->request->has('startDate') && !empty($request->request->get('startDate'))
            ){
                $category = $em->getRepository(RentalCostCategory::class)->find($request->request->get('category'));
                if($category instanceof RentalCostCategory){
                    $oldStartDate = $rentalCost->getStartDate();
                    $startDate = new \DateTime($request->request->get('startDate'));
                    $rentalCost->setCategory($category);
                    $rentalCost->setAmount($request->request->get('amount'));
                    $rentalCost->setStartDate($startDate);
                    $em->persist($rentalCost);
                    $em->flush();

//                    $percentage = LeaseHelper::getLeaseRentalPercentage($em, $rentalCost->getLease());
                    $currentAmount = $rentalCost->getAmount();
                    $now = new \DateTime();
                    $totalStatus = false;
                    if($rentalCost->getStartDate()->format('Y-m-d') <= $now->format('Y-m-d')){
//                        $currentAmount += round((($rentalCost->getAmount() * $percentage) / 100), 2);
                        $totalStatus = true;
                    }
                    $oldTotalStatus = false;
                    if($oldStartDate->format('Y-m-d') <= $now->format('Y-m-d')){
                        $oldTotalStatus = true;
                    }

                    return new JsonResponse([
                        'result'=>true,
                        'id'=>$rentalCost->getId(),
                        'category'=>[
                            'id'=>$rentalCost->getCategory()->getId(),
                            'name'=>$rentalCost->getCategory()->getName()
                        ],
                        'amount'=>$rentalCost->getAmount(),
                        'currentAmount'=>$currentAmount,
                        'startDate'=>$rentalCost->getStartDate()->format('d M Y'),
                        'totalStatus'=>$totalStatus,
                        'oldTotalStatus'=>$oldTotalStatus,
                    ]);
                }
            }
        }
        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/addendum/remove/{id}", name="lease_remove_addendum", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function removeAddendumByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Lease', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        $rentalCost = $em->getRepository(LeaseRentalCost::class)->find($id);
        if($rentalCost instanceof LeaseRentalCost){
            $oldStartDate = $rentalCost->getStartDate();
            $oldTotalStatus = false;
            $now = new \DateTime();
            if($oldStartDate->format('Y-m-d') <= $now->format('Y-m-d')){
                $oldTotalStatus = true;
            }

            $em->remove($rentalCost);
            $em->flush();

            return new JsonResponse(['result'=>true, 'oldTotalStatus'=>$oldTotalStatus]);
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/agentDetails/{id}", name="lease_get_agent_data", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function getAgentDataBySiteIdAction($id){
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository(Site::class)->find($id);
        if($site instanceof Site){
            $previousLease = $em->getRepository(Lease::class)->findOneBy(['site'=>$site],['endDate'=>'DESC']);
            if($previousLease instanceof Lease){
                $proposedLease = LeaseHelper::getProposedLeaseByPrevious($em, $previousLease);
                $oldPercentage = LeaseHelper::getLeaseRentalPercentage($em, $previousLease);

                return new JsonResponse(['result'=>true, 'proposedLease'=>$proposedLease, 'oldPercentage'=>$oldPercentage]);
            }
        }

        return new JsonResponse(['result'=>false]);
    }
}
