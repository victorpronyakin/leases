<?php

namespace App\Controller;

use App\Entity\Financial;
use App\Entity\FinancialStatus;
use App\Entity\LandlordDocumentStatus;
use App\Entity\Lease;
use App\Helper\FinancialHelper;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FinancialController
 * @package App\Controller
 *
 * @Route("/financial")
 */
class FinancialController extends AbstractController
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
     * @param PaginatorInterface $paginator
     * @return Response
     * @throws \Exception
     *
     * @Route("/", name="financial_list")
     */
    public function indexAction(Request $request, PaginatorInterface $paginator)
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Financial', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $finances = FinancialHelper::generateFinancialData($em, $request->query->get('month'), $request->query->all());
        $pagination = $paginator->paginate(
            $finances,
            ($request->query->getInt('page') > 0) ? $request->query->getInt('page') : 1,
            ($request->query->getInt('limit') > 0) ? $request->query->getInt('limit') : 25
        );

        $financialStatuses = $em->getRepository(FinancialStatus::class)->findAll();

        return $this->render('sites_and_leases/financial/index.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'financial',
            'financialStatuses' => $financialStatuses,
            'pagination' => $pagination,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Financial'),
        ]);
    }

    /**
     * @param Request $request
     * @throws \Exception
     *
     * @Route("/download", name="financial_list_download")
     */
    public function downloadAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $finances = FinancialHelper::generateFinancialData($em, $request->query->get('month'), $request->query->all());

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Site ID')
            ->setCellValue('B1', 'Site Name')
            ->setCellValue('C1', 'Discrepancy')
            ->setCellValue('D1', 'Discrepancy %')
            ->setCellValue('E1', "Change Invoice amount")
            ->setCellValue('F1', "Change Invoice amount %")
            ->setCellValue('G1', "Change Electricity amount")
            ->setCellValue('H1', "Change Electricity amount %");

        if(!empty($finances)){
            $currencySymbol = $this->settingService->getCurrencySymbol();
            $i = 1;
            $prevMonth = new \DateTime($request->request->get('month'));
            $prevMonth->modify('-1 month');
            foreach ($finances as $finance){
                $i++;
                $discrepancy = round(abs($finance['leaseExpense']-$finance['leaseCharge']), 2);
                $discrepancyPercentage = round(abs(($finance['leaseExpense']-$finance['leaseCharge'])/(($finance['leaseExpense']+$finance['leaseCharge'])/2))*100, 2);

                $previousFinancial = FinancialHelper::generateLeaseFinancialData($em, $finance['lease'], $prevMonth->format('F Y'));

                $diffInvoice = round(abs($finance['leaseCharge']-$previousFinancial['leaseCharge']), 2);
                if($finance['leaseCharge'] == 0 && $previousFinancial['leaseCharge'] == 0){
                    $diffInvoicePercentage = 0;
                }
                elseif($finance['leaseCharge'] == 0 || $previousFinancial['leaseCharge'] == 0){
                    $diffInvoicePercentage = 100;
                }
                else{
                    $diffInvoicePercentage = round(abs(($finance['leaseCharge']-$previousFinancial['leaseCharge'])/(($finance['leaseCharge']+$previousFinancial['leaseCharge'])/2))*100, 2);
                }
                $diffElectricity = round(abs($finance['electricityCost']-$previousFinancial['electricityCost']), 2);
                if($finance['electricityCost'] == 0 && $previousFinancial['electricityCost'] == 0){
                    $diffElectricityPercentage = 0;
                }
                elseif($finance['electricityCost'] == 0 || $previousFinancial['electricityCost'] == 0){
                    $diffElectricityPercentage = 100;
                }
                else{
                    $diffElectricityPercentage = round(abs(($finance['electricityCost']-$previousFinancial['electricityCost'])/(($finance['electricityCost']+$previousFinancial['electricityCost'])/2))*100, 2);
                }

                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $finance['lease']->getSite()->getNumber())
                    ->setCellValue('B'.$i, $finance['lease']->getSite()->getName())
                    ->setCellValue('C'.$i, $currencySymbol." ".$discrepancy)
                    ->setCellValue('D'.$i, $discrepancyPercentage)
                    ->setCellValue('E'.$i, $currencySymbol." ".$diffInvoice)
                    ->setCellValue('F'.$i, $diffInvoicePercentage)
                    ->setCellValue('G'.$i, $currencySymbol." ".$diffElectricity)
                    ->setCellValue('H'.$i, $diffElectricityPercentage);
            }
        }



        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="FinancialReports_'.$request->query->get("month").'.xls"');
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
     * @throws \Exception
     *
     * @Route("/edit", name="financial_edit", methods={"POST"})
     */
    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Financial', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if($request->request->has('leaseId') && !empty($request->request->get('leaseId')) && $request->request->has('month') && !empty($request->request->get('month'))){
            $lease = $em->getRepository(Lease::class)->find($request->request->get('leaseId'));
            if($lease instanceof Lease){
                $financial = $em->getRepository(Financial::class)->findOneBy(['lease'=>$lease, 'month'=>$request->request->get('month')]);
                if(!$financial instanceof Financial){
                    $financial = new Financial($request->request->get('month'), $lease);
                }
                if($request->request->has('fields') && !empty($request->request->get('fields'))){
                    $leasePaymentStatusOld = $financial->getLeasePaymentStatus();
                    $electricityPaymentStatusOld = $financial->getElectricityPaymentStatus();
                    $otherCostPaymentStatusOld= $financial->getOtherCostPaymentStatus();

                    $financial->update($request->request->get('fields'));

                    if(!empty($financial->getLeasePaymentStatus()) && !$financial->getLeasePaymentStatus() instanceof FinancialStatus){
                       $paymentStatus = $em->getRepository(FinancialStatus::class)->find($financial->getLeasePaymentStatus());
                       if($paymentStatus instanceof FinancialStatus){
                           $financial->setLeasePaymentStatus($paymentStatus);
                       }
                       else{
                           $financial->setLeasePaymentStatus(null);
                       }
                    }
                    if($leasePaymentStatusOld instanceof FinancialStatus && $financial->getLeasePaymentStatus() instanceof FinancialStatus){
                        if($leasePaymentStatusOld->getId() != $financial->getLeasePaymentStatus()->getId() || empty($financial->getLeasePaymentStatusUpdated())){
                            $financial->setLeasePaymentStatusUpdated(new \DateTime());
                        }
                    }
                    else{
                        $financial->setLeasePaymentStatusUpdated(new \DateTime());
                    }

                    if($financial->getElectricityPaymentStatus() && !$financial->getElectricityPaymentStatus() instanceof FinancialStatus){
                        $paymentStatus = $em->getRepository(FinancialStatus::class)->find($financial->getElectricityPaymentStatus());
                        if($paymentStatus instanceof FinancialStatus){
                            $financial->setElectricityPaymentStatus($paymentStatus);
                        }
                        else{
                            $financial->setElectricityPaymentStatus(null);
                        }
                    }
                    if($electricityPaymentStatusOld instanceof FinancialStatus && $financial->getElectricityPaymentStatus() instanceof FinancialStatus){
                        if($electricityPaymentStatusOld->getId() != $financial->getElectricityPaymentStatus()->getId() || empty($financial->getElectricityPaymentStatusUpdated())){
                            $financial->setElectricityPaymentStatusUpdated(new \DateTime());
                        }
                    }
                    else{
                        $financial->setElectricityPaymentStatusUpdated(new \DateTime());
                    }

                    if($financial->getOtherCostPaymentStatus() && !$financial->getOtherCostPaymentStatus() instanceof FinancialStatus){
                        $paymentStatus = $em->getRepository(FinancialStatus::class)->find($financial->getOtherCostPaymentStatus());
                        if($paymentStatus instanceof FinancialStatus){
                            $financial->setOtherCostPaymentStatus($paymentStatus);
                        }
                        else{
                            $financial->setOtherCostPaymentStatus(null);
                        }
                    }
                    if($otherCostPaymentStatusOld instanceof FinancialStatus && $financial->getOtherCostPaymentStatus() instanceof FinancialStatus){
                        if($otherCostPaymentStatusOld->getId() != $financial->getOtherCostPaymentStatus()->getId() || empty($financial->getOtherCostPaymentStatusUpdated())){
                            $financial->setOtherCostPaymentStatusUpdated(new \DateTime());
                        }
                    }
                    else{
                        $financial->setOtherCostPaymentStatusUpdated(new \DateTime());
                    }

                    $em->persist($financial);
                    $em->flush();
                }

                if($request->files->has('invoice') && $request->files->get('invoice') instanceof UploadedFile){
                    $fileUpload = $request->files->get('invoice');
                    $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                    $originalName = $fileUpload->getClientOriginalName();
                    $path = "upload/financial";
                    $filePath = $request->getSchemeAndHttpHost()."/".$path."/".$fileName;
                    try {
                        $fileUpload->move($path, $fileName);
                    } catch (\Exception $e) {
                        return new JsonResponse(['result'=>false]);
                    }

                    $invoices = $financial->getInvoices();
                    $invoices[] = [
                        'url' => $filePath,
                        'name' => $originalName
                    ];
                    $financial->setInvoices($invoices);
                    $em->persist($financial);
                    $em->flush();

                    return new JsonResponse(['result'=>true, 'url'=>$filePath, 'name'=>$originalName]);
                }

                return new JsonResponse(['result'=>true]);
            }
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/removeInvoice", name="financial_remove_invoice", methods={"POST"})
     */
    public function removeInvoiceAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Financial', 'editable')) {
            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }

        if($request->request->has('leaseId') && !empty($request->request->get('leaseId')) && $request->request->has('month') && !empty($request->request->get('month'))){
            $lease = $em->getRepository(Lease::class)->find($request->request->get('leaseId'));
            if($lease instanceof Lease){
                $financial = $em->getRepository(Financial::class)->findOneBy(['lease'=>$lease, 'month'=>$request->request->get('month')]);
                if($financial instanceof Financial){
                    if($request->request->has('url') && !empty($request->request->get('url'))){
                        $invoices = [];

                        foreach ($financial->getInvoices() as $invoice){
                            if(isset($invoice['url']) && !empty($invoice['url']) && $invoice['url'] != $request->request->get('url')){
                                $invoices[] = $invoice;
                            }
                        }

                        $financial->setInvoices($invoices);
                        $em->persist($financial);
                        $em->flush();

                        return new JsonResponse(['result'=>true]);
                    }
                }
            }
        }

        return new JsonResponse(['result'=>false]);
    }

}
