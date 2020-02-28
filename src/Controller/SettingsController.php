<?php

namespace App\Controller;

use App\Entity\CPIRate;
use App\Entity\Currency;
use App\Entity\FinancialStatus;
use App\Entity\HoursOfAccess;
use App\Entity\IssueType;
use App\Entity\LandlordContactType;
use App\Entity\LandlordDocumentStatus;
use App\Entity\LandlordDocumentType;
use App\Entity\LandlordType;
use App\Entity\LeaseDepositType;
use App\Entity\LeaseDocumentType;
use App\Entity\LeaseType;
use App\Entity\ManagementStatus;
use App\Entity\RentalCostCategory;
use App\Entity\SiteInventoryCategory;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingsController
 * @package App\Controller
 *
 * @Route("/settings")
 */
class SettingsController extends AbstractController
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
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="settings_list", methods={"GET", "POST"})
     */
    public function generalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        if($request->request->has('submit_currency')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
                if ($request->request->has('currency')) {
                    $this->settingService->setValue('currency', $request->request->get('currency'));

                    $this->addFlash('success', 'Currency has been updated!');
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }

        $generalSettings = [
            [
                'add' => 'New Lease Type',
                'edit' => 'Edit Lease Type',
                'label' => 'Lease Types',
                'class' => 'leaseType',
                'items' => $em->getRepository(LeaseType::class)->findAll()
            ],
            [
                'add' => 'New Rental Cost Category',
                'edit' => 'Edit Rental Cost Category',
                'label' => 'Rental Cost Categories',
                'class' => 'rentalCostCategory',
                'items' => $em->getRepository(RentalCostCategory::class)->findAll()
            ],
            [
                'add' => 'New Deposit Type',
                'edit' => 'Edit Deposit Type',
                'label' => 'Deposit Types',
                'class' => 'depositType',
                'items' => $em->getRepository(LeaseDepositType::class)->findAll()
            ],
            [
                'add' => 'New Lease Supporting Document Type',
                'edit' => 'Edit Lease Supporting Document Type',
                'label' => 'Lease Supporting Document Types',
                'class' => 'leaseDocumentType',
                'items' => $em->getRepository(LeaseDocumentType::class)->findAll()
            ],
            [
                'add' => 'New Supporting Document Status',
                'edit' => 'Edit Supporting Document Status',
                'label' => 'Supporting Document Statuses',
                'class' => 'documentStatus',
                'items' => $em->getRepository(LandlordDocumentStatus::class)->findAll()
            ],
            [
                'add' => 'New Site Status',
                'edit' => 'Edit Site Status',
                'label' => 'Site Statuses',
                'class' => 'siteStatus',
                'items' => $em->getRepository(ManagementStatus::class)->findAll()
            ],
            [
                'add' => 'New Hours of access',
                'edit' => 'Edit Hours of access',
                'label' => 'Hours of access',
                'class' => 'hoursOfAccess',
                'items' => $em->getRepository(HoursOfAccess::class)->findAll()
            ],
            [
                'add' => 'New Site Inventory Category',
                'edit' => 'Edit Site Inventory Category',
                'label' => 'Site Inventory Categories',
                'class' => 'inventoryCategory',
                'items' => $em->getRepository(SiteInventoryCategory::class)->findAll()
            ],
            [
                'add' => 'New Landlord Type',
                'edit' => 'Edit Landlord Type',
                'label' => 'Landlord Types',
                'class' => 'landlordType',
                'items' => $em->getRepository(LandlordType::class)->findAll()
            ],
            [
                'add' => 'New Landlord Supporting Document Type',
                'edit' => 'Edit Landlord Supporting Document Type',
                'label' => 'Landlord Supporting Document Types',
                'class' => 'landlordDocumentType',
                'items' => $em->getRepository(LandlordDocumentType::class)->findAll()
            ],
            [
                'add' => 'New Landlord Contacts Type',
                'edit' => 'Edit Landlord Contacts Type',
                'label' => 'Landlord Contacts Types',
                'class' => 'landlordContactType',
                'items' => $em->getRepository(LandlordContactType::class)->findAll()
            ],
            [
                'add' => 'New Financial Status Type',
                'edit' => 'Edit Financial Status Type',
                'label' => 'Financial Status Types',
                'class' => 'financialStatusType',
                'items' => $em->getRepository(FinancialStatus::class)->findAll()
            ],
            [
                'add' => 'New Issue Type',
                'edit' => 'Edit Issue Type',
                'label' => 'Issue Types',
                'class' => 'issueType',
                'items' => $em->getRepository(IssueType::class)->findAll()
            ]
        ];


        if($request->request->has('submit_main_dashboard')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
                $this->settingService->setValue('mainDashboardStatus', $request->request->get('mainDashboardStatus'), true);

                $this->addFlash('success', 'Updated!');
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }

        if($request->request->has('submit_agent_dashboard')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
                $this->settingService->setValue('mainAgentStatus', $request->request->get('mainAgentStatus'), true);

                $this->addFlash('success', 'Updated!');
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }


        return $this->render('settings/general.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'general',
            'currencyValue' => $this->settingService->getValue('currency'),
            'currencies' => $em->getRepository(Currency::class)->findAll(),
            'generalSettings' => $generalSettings,
            'siteStatuses' => $em->getRepository(ManagementStatus::class)->findAll(),
            'mainDashboardStatus' => $this->settingService->getValue('mainDashboardStatus', true),
            'mainAgentStatus' => $this->settingService->getValue('mainAgentStatus', true),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Setting'),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     *
     * @Route("/cpi_rates", name="settings_cpi_rates", methods={"GET", "POST"})
     */
    public function cpiRatesAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        if($request->request->has('submit_cpi_rates')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
                if($request->request->has('cpiRates') && !empty($request->request->get('cpiRates'))){
                    foreach ($request->request->get('cpiRates') as $month => $value){
                        $cpiRate = $em->getRepository(CPIRate::class)->findOneBy(['month'=>$month]);
                        if($cpiRate instanceof CPIRate){
                            $cpiRate->setValue($value);
                        }
                        else{
                            $cpiRate = new CPIRate($month, $value);
                        }
                        $em->persist($cpiRate);
                    }
                    $em->flush();
                    $this->addFlash('success', 'CPI Rates has been updated!');
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }

        }

        $cpiRates = [];
        $cpiRatesResult = $em->getRepository(CPIRate::class)->findAll();
        if(!empty($cpiRatesResult)){
            foreach ($cpiRatesResult as $cpiRate){
                if($cpiRate instanceof CPIRate){
                    $cpiRates[$cpiRate->getMonth()] = $cpiRate->getValue();
                }
            }
        }

        return $this->render('settings/cpi_rates.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'cpi_rates',
            'cpiRates' => $cpiRates,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Setting'),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/add", name="settings_general_item_add", methods={"POST"})
     */
    public function addGeneralSettingItemAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        if(!UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if($request->request->has('type') && !empty($request->request->get('type')) && $request->request->has('name') && !empty($request->request->get('name'))){
            $item = null;

            switch ($request->request->get('type')){
                case 'leaseType':
                    $item = new LeaseType($request->request->get('name'));
                    break;
                case 'rentalCostCategory':
                    $item = new RentalCostCategory($request->request->get('name'));
                    break;
                case 'depositType':
                    $item = new LeaseDepositType($request->request->get('name'));
                    break;
                case 'leaseDocumentType':
                    $item = new LeaseDocumentType($request->request->get('name'));
                    break;
                case 'documentStatus':
                    $item = new LandlordDocumentStatus($request->request->get('name'));
                    break;
                case 'siteStatus':
                    $item = new ManagementStatus($request->request->get('name'));
                    break;
                case 'hoursOfAccess':
                    $item = new HoursOfAccess($request->request->get('name'));
                    break;
                case 'inventoryCategory':
                    $item = new SiteInventoryCategory($request->request->get('name'));
                    break;
                case 'landlordType':
                    $item = new LandlordType($request->request->get('name'));
                    break;
                case 'landlordDocumentType':
                    $item = new LandlordDocumentType($request->request->get('name'));
                    break;
                case 'landlordContactType':
                    $item = new LandlordContactType($request->request->get('name'));
                    break;
                case 'financialStatusType':
                    $item = new FinancialStatus($request->request->get('name'));
                    break;
                case 'issueType':
                    $item = new IssueType($request->request->get('name'));
                    break;
            }

            if(!is_null($item)){
                $em->persist($item);
                $em->flush();

                return new JsonResponse([
                    'result'=>true,
                    'id'=>$item->getId(),
                    'name'=>$item->getName()
                ]);
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/edit", name="settings_general_item_edit", methods={"POST"})
     */
    public function editGeneralSettingItemAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        if(!UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if(
            $request->request->has('type') && !empty($request->request->get('type'))
            && $request->request->has('id') && !empty($request->request->get('id'))
            && $request->request->has('name') && !empty($request->request->get('name'))
        ){
            $item = null;

            switch ($request->request->get('type')){
                case 'leaseType':
                    $item = $em->getRepository(LeaseType::class)->find($request->request->get('id'));
                    break;
                case 'rentalCostCategory':
                    $item = $em->getRepository(RentalCostCategory::class)->find($request->request->get('id'));
                    break;
                case 'depositType':
                    $item = $em->getRepository(LeaseDepositType::class)->find($request->request->get('id'));
                    break;
                case 'leaseDocumentType':
                    $item = $em->getRepository(LeaseDocumentType::class)->find($request->request->get('id'));
                    break;
                case 'documentStatus':
                    $item = $em->getRepository(LandlordDocumentStatus::class)->find($request->request->get('id'));
                    break;
                case 'siteStatus':
                    $item = $em->getRepository(ManagementStatus::class)->find($request->request->get('id'));
                    break;
                case 'hoursOfAccess':
                    $item = $em->getRepository(HoursOfAccess::class)->find($request->request->get('id'));
                    break;
                case 'inventoryCategory':
                    $item = $em->getRepository(SiteInventoryCategory::class)->find($request->request->get('id'));
                    break;
                case 'landlordType':
                    $item = $em->getRepository(LandlordType::class)->find($request->request->get('id'));
                    break;
                case 'landlordDocumentType':
                    $item = $em->getRepository(LandlordDocumentType::class)->find($request->request->get('id'));
                    break;
                case 'landlordContactType':
                    $item = $em->getRepository(LandlordContactType::class)->find($request->request->get('id'));
                    break;
                case 'financialStatusType':
                    $item = $em->getRepository(FinancialStatus::class)->find($request->request->get('id'));
                    break;
                case 'issueType':
                    $item = $em->getRepository(IssueType::class)->find($request->request->get('id'));
                    break;
            }

            if(!empty($item)){
                $item->setName($request->request->get('name'));
                $em->persist($item);
                $em->flush();

                return new JsonResponse(['result'=>true]);
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/remove", name="settings_general_item_remove", methods={"POST"})
     */
    public function removeGeneralSettingItemAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        if(!UserHelper::checkPermission($em, $this->getUser(), 'Setting', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if($request->request->has('type') && !empty($request->request->get('type')) && $request->request->has('id') && !empty($request->request->get('id'))){
            $item = null;

            switch ($request->request->get('type')){
                case 'leaseType':
                    $item = $em->getRepository(LeaseType::class)->find($request->request->get('id'));
                    break;
                case 'rentalCostCategory':
                    $item = $em->getRepository(RentalCostCategory::class)->find($request->request->get('id'));
                    break;
                case 'depositType':
                    $item = $em->getRepository(LeaseDepositType::class)->find($request->request->get('id'));
                    break;
                case 'leaseDocumentType':
                    $item = $em->getRepository(LeaseDocumentType::class)->find($request->request->get('id'));
                    break;
                case 'documentStatus':
                    $item = $em->getRepository(LandlordDocumentStatus::class)->find($request->request->get('id'));
                    break;
                case 'siteStatus':
                    $item = $em->getRepository(ManagementStatus::class)->find($request->request->get('id'));
                    break;
                case 'hoursOfAccess':
                    $item = $em->getRepository(HoursOfAccess::class)->find($request->request->get('id'));
                    break;
                case 'inventoryCategory':
                    $item = $em->getRepository(SiteInventoryCategory::class)->find($request->request->get('id'));
                    break;
                case 'landlordType':
                    $item = $em->getRepository(LandlordType::class)->find($request->request->get('id'));
                    break;
                case 'landlordDocumentType':
                    $item = $em->getRepository(LandlordDocumentType::class)->find($request->request->get('id'));
                    break;
                case 'landlordContactType':
                    $item = $em->getRepository(LandlordContactType::class)->find($request->request->get('id'));
                    break;
                case 'financialStatusType':
                    $item = $em->getRepository(FinancialStatus::class)->find($request->request->get('id'));
                    break;
                case 'issueType':
                    $item = $em->getRepository(IssueType::class)->find($request->request->get('id'));
                    break;
            }

            if(!empty($item)){
                $em->remove($item);
                $em->flush();

                return new JsonResponse(['result'=>true]);
            }
        }

        return new JsonResponse(['result'=>false]);
    }
}
