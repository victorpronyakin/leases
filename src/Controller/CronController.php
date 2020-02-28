<?php


namespace App\Controller;


use App\Entity\Site;
use App\Service\ContactReminderService;
use App\Service\ReminderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class CronController
 * @package App\Controller
 *
 * @Route("/cron")
 */
class CronController extends AbstractController
{
    /**
     * @param ReminderService $reminderService
     * @return Response
     * @throws \Exception
     *
     * @Route("/reminder", name="cron_reminder", methods={"GET"})
     */
    public function remindersAction(ReminderService $reminderService)
    {
        try{
            $reminderService->handleReminders();
        }
        catch (\Exception $e){}

        return new Response(null);
    }

    /**
     * @param ContactReminderService $contactReminderService
     * @return Response
     *
     * @Route("/contact_reminder", name="cron_contact_reminder", methods={"GET"})
     */
    public function contactReminderAction(ContactReminderService $contactReminderService){
        try{
            $contactReminderService->handler();
        }
        catch (\Exception $e){}

        return new Response(null);
    }
}
