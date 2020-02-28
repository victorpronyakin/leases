<?php

namespace App\Controller;

use App\Entity\ActualReminder;
use App\Entity\Issue;
use App\Entity\IssueType;
use App\Entity\Reminder;
use App\Entity\Site;
use App\Helper\UserHelper;
use App\Service\ReminderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class IssueController
 * @package App\Controller
 *
 * @Route("/issue")
 */
class IssueController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="issue_list", methods={"GET"})
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $openIssues = $em->getRepository(Issue::class)->findBy(['status'=>true], ['logged'=>'DESC']);
        $closeIssues = $em->getRepository(Issue::class)->findBy(['status'=>false], ['logged'=>'DESC']);
        $issueTypes = $em->getRepository(IssueType::class)->findAll();


        return $this->render('issue/index.html.twig',[
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'issue',
            'openIssues' => $openIssues,
            'closeIssues' => $closeIssues,
            'issueTypes' => $issueTypes,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Issue'),
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ReminderService $reminderService
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/add", name="issue_add", methods={"GET", "POST"})
     */
    public function addAction(Request $request, ValidatorInterface $validator, ReminderService $reminderService){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'added')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        $issue = new Issue();
        if($request->request->has('submit')){
            if($request->request->has('site') && !empty($request->request->get('site'))){
                $site = $em->getRepository(Site::class)->find($request->request->get('site'));
                if($site instanceof Site){
                    $issue->setSite($site);
                }
            }

            if($request->request->has('type') && !empty($request->request->get('type'))){
                $type = $em->getRepository(IssueType::class)->find($request->request->get('type'));
                if($type instanceof IssueType){
                    $issue->setType($type);
                }
            }

            $issue->setDetails($request->request->get('details'));

            $errors = $validator->validate($issue);
            if(count($errors) === 0){
                $em->persist($issue);
                $em->flush();

                $reminders = $em->getRepository(Reminder::class)->findBy(['type'=>4]);
                if(!empty($reminders)){
                    foreach ($reminders as $reminder){
                        if($reminder instanceof Reminder){
                            $detail = $reminder->getDetail();
                            if(isset($detail['issue']) && $detail['issue'] == 'immediately'){
                                $actualReminder = new ActualReminder($reminder, $issue->getSite(), null, null, $issue);
                                $em->persist($actualReminder);
                                $em->flush();

                                $reminderService->sendReminders($actualReminder);
                            }
                        }
                    }
                }

                $this->addFlash('success', "Issue has been added!");

                return $this->redirectToRoute('issue_list');
            }
            else {
                foreach ($errors as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        $issueTypes = $em->getRepository(IssueType::class)->findAll();
        $sites = $em->getRepository(Site::class)->findAll();

        return $this->render('issue/add.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'issue',
            'issue' => $issue,
            'issueTypes' => $issueTypes,
            'sites' => $sites,
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param $id
     * @return RedirectResponse|Response
     *
     * @Route("/edit/{id}", name="issue_edit", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function editByIdAction(Request $request, ValidatorInterface $validator, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'viewable') && !UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $issue = $em->getRepository(Issue::class)->find($id);
        if(!$issue instanceof Issue){

            $this->addFlash('error', 'Issue not found');
            return $this->redirectToRoute('issue_list');
        }

        if($request->request->has('submit')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'editable')) {
                if($request->request->has('type') && !empty($request->request->get('type'))){
                    $type = $em->getRepository(IssueType::class)->find($request->request->get('type'));
                    if($type instanceof IssueType){
                        $issue->setType($type);
                    }
                }

                $issue->setDetails($request->request->get('details'));
                if($request->request->has('status')){
                    if($request->request->get('status') == true){
                        $issue->setStatus(true);
                    }
                    else{
                        $issue->setStatus(false);
                    }
                }

                $errors = $validator->validate($issue);
                if(count($errors) === 0){
                    $em->persist($issue);
                    $em->flush();

                    $this->addFlash('success', "Issue has been edited!");
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

        $issueTypes = $em->getRepository(IssueType::class)->findAll();
        $sites = $em->getRepository(Site::class)->findAll();

        return $this->render('issue/edit.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'issue',
            'issue' => $issue,
            'issueTypes' => $issueTypes,
            'sites' => $sites,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Issue'),
            'userPermissionSite' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Site'),
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", name="issue_remove", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function removeByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'removable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('issue_list');
        }
        $issue = $em->getRepository(Issue::class)->find($id);
        if($issue instanceof Issue){
            $em->remove($issue);
            $em->flush();

            $this->addFlash('success', 'Issue has been removed');
        }
        else{
            $this->addFlash('error', 'Issue not found');
        }

        return $this->redirectToRoute('issue_list');
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     *
     * @Route("/change_status/{id}", name="issue_change_status", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function changeStatusByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Issue', 'editable')) {

            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        $issue = $em->getRepository(Issue::class)->find($id);
        if($issue instanceof Issue){
            if($request->request->has('status') && !empty($request->request->get('status'))){
                if($request->request->get('status') == 'true'){
                    $issue->setStatus(true);
                }
                else{
                    $issue->setStatus(false);
                }
                $em->persist($issue);
                $em->flush();

                return new JsonResponse(['result'=>true]);
            }
        }

        return new JsonResponse(['result'=>false]);
    }
}
