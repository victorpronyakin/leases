<?php

namespace App\Controller;

use App\Entity\Lease;
use App\Helper\LeaseHelper;
use App\Service\ReportService;
use App\Service\SettingService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reporting")
 */
class ReportingController extends AbstractController
{
    /**
     * @var ReportService
     */
    public $reportService;

    /**
     * @var SettingService
     */
    public $settingService;

    /**
     * ReportingController constructor.
     * @param ReportService $reportService
     * @param SettingService $settingService
     */
    public function __construct(ReportService $reportService, SettingService $settingService)
    {
        $this->reportService = $reportService;
        $this->settingService = $settingService;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Route("/", name="reports_list", methods={"GET"})
     */
    public function indexAction(Request $request)
    {

        return $this->render('reporting/index.html.twig', [
            'active_menu1' => 'reports',
            'allFields' => $this->reportService->allFields,
            'allOperators' => $this->reportService->allOperators,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'result' => $this->reportService->getResult($request->query->get('filters'), $request->query->get('fields'))
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     *
     * @Route("/download", name="reports_list_download", methods={"GET"})
     */
    public function downloadAction(Request $request){
        $result = $this->reportService->getResult($request->query->get('filters'), $request->query->get('fields'));
        if(isset($result['fields']) && !empty($result['fields']) && isset($result['items']) && !empty($result['items'])){
            $currencySymbol = $this->settingService->getCurrencySymbol();
            $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA'];
            $spreadsheet = new Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $rowKey = 1;
            $headerKey = 0;
            foreach ($result['fields'] as $field){
                $spreadsheet->getActiveSheet()->setCellValue($columnLetters[$headerKey].$rowKey, $field);
                $spreadsheet->getActiveSheet()->getColumnDimension($columnLetters[$headerKey])->setAutoSize(true);;
                $headerKey++;
            }

            foreach ($result['items'] as $item){
                $rowKey++;
                $columnKey=0;
                foreach ($result['fields'] as $fieldKey=>$fieldName){
                    if(in_array($fieldKey, ['electricityFixed', 'depositAmount', 'proposedLease', 'targetRenewalRental', 'agentSaving', 'agentBilling'])){
                        $spreadsheet->getActiveSheet()->setCellValue($columnLetters[$columnKey].$rowKey, (isset($item[$fieldKey])) ? (!empty($item[$fieldKey])) ? $currencySymbol.' '.$item[$fieldKey] : $item[$fieldKey] : '');
                    }
                    else{
                        $spreadsheet->getActiveSheet()->setCellValue($columnLetters[$columnKey].$rowKey, (isset($item[$fieldKey])) ? $item[$fieldKey] : '');
                    }
                    $columnKey++;
                }
            }

            $now = new \DateTime();
            // Redirect output to a client’s web browser (Xls)
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Reports_'.$now->format('Y-m-d_H-i').'.xls"');
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

        return $this->redirectToRoute('reports_list', $request->query->all());
    }
}
