<?php

namespace App\Controller;

use App\Entity\ActualReminder;
use App\Entity\Lease;
use App\Entity\ManagementStatus;
use App\Entity\Setting;
use App\Entity\Site;
use App\Helper\LeaseHelper;
use App\Helper\ReminderHelper;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MainController
 * @package App\Controller
 */
class MainController extends AbstractController
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
     * @Route("/dashboard", name="dashboard", methods={"GET"})
     */
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $leasesExp = $em->getRepository(Lease::class)->countAllWithParams(['expire'=>60]);
        $leases = $em->getRepository(Lease::class)->findAllWithParams();
        $avgCost = 0;
        $avgEscalation = 0;
        $leasesTotal = count($leases);
        if($leasesTotal > 0){
            foreach ($leases as $lease){
                if($lease instanceof Lease){
                    $avgCost += LeaseHelper::getCurrentTotalMonthlyCost($em, $lease);
                    $avgEscalation += LeaseHelper::getLeaseRentalPercentage($em, $lease);
                }
            }
            $avgCost = round($avgCost/$leasesTotal,2);
            $avgEscalation = round($avgEscalation/$leasesTotal,2);
        }

        $activeRemindersResult = $em->getRepository(ActualReminder::class)->getAllByParams(true, true);
        $activeReminders = ReminderHelper::generateActualRemindersDataForList($em, $activeRemindersResult);
        $historicalRemindersResult = $em->getRepository(ActualReminder::class)->getAllByParams(false, true);
        $historicalReminders = ReminderHelper::generateActualRemindersDataForList($em, $historicalRemindersResult);
        $snoozedRemindersResult = $em->getRepository(ActualReminder::class)->getAllByParams(true, true, true);
        $snoozedReminders = ReminderHelper::generateActualRemindersDataForList($em, $snoozedRemindersResult);

        $mainDashboardStatuses = [];
        $mainDashboardSetting = $em->getRepository(Setting::class)->findOneBy(['name'=>'mainDashboardStatus']);
        if($mainDashboardSetting instanceof Setting){
            $mainDashboardStatus = json_decode($mainDashboardSetting->getValue(), true);
            if(!empty($mainDashboardStatus)){
                foreach ($mainDashboardStatus as $statusId){
                    $siteStatus = $em->getRepository(ManagementStatus::class)->find($statusId);
                    if($siteStatus instanceof ManagementStatus){
                        $mainDashboardStatuses[] = [
                            'label' => $siteStatus->getName(),
                            'count' => $em->getRepository(Site::class)->count(['siteStatus'=>$siteStatus])
                        ];
                    }
                }
            }
        }

        return $this->render('main/dashboard.html.twig', [
            'active_menu1' => 'dashboard',
            'leasesTotal' => $leasesTotal,
            'leasesExp' => $leasesExp,
            'avgCost' => $avgCost,
            'avgEscalation' => $avgEscalation,
            'activeReminders' => $activeReminders,
            'historicalReminders' => $historicalReminders,
            'snoozedReminders' => $snoozedReminders,
            'mainDashboardStatuses' => $mainDashboardStatuses,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Reminder')
        ]);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     *
     * @Route("/dashboardChart", name="dashboard_chart", methods={"GET"})
     */
    public function dashboardChartAction(){
        $em = $this->getDoctrine()->getManager();
        $dataProvider = [];
        for ($i=6; $i>0; $i--){
            $date = new \DateTime('-'.$i.' month');
            $total = 0;
            $leases = $em->getRepository(Lease::class)->findAllWithParams(['date'=>$date]);
            if(!empty($leases)){
                foreach ($leases as $lease){
                    if($lease instanceof Lease){
                        $total += LeaseHelper::getCurrentTotalMonthlyCost($em, $lease, $date);
                    }
                }
            }
            $dataProvider[] = [
                'month' => $date->format('F Y'),
                'total' => round($total, 2),
            ];
        }

        return new JsonResponse(['result'=>true, 'dataProvider'=>$dataProvider]);
    }
}
