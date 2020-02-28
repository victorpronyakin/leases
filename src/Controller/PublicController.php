<?php

namespace App\Controller;

use App\Entity\LandlordContact;
use App\Entity\Site;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PublicController
 * @package App\Controller
 *
 * @Route("/public")
 */
class PublicController extends AbstractController
{
    /**
     * @param $type
     * @return Response
     *
     * @Route("/reminder/{type}", name="public_reminder_type", methods={"GET"})
     */
    public function reminderAllCorrectAction($type)
    {
        return $this->render('public/index.html.twig', [
            'type' => $type
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/reminder/no_longer/contact/{id}", name="public_reminder_no_longer_contact", methods={"GET"})
     */
    public function noLongerContactAction($id){
        $em = $this->getDoctrine()->getManager();
        $contact = $em->getRepository(LandlordContact::class)->find($id);
        if($contact instanceof LandlordContact){
            $em->remove($contact);
            $em->flush();
        }

        return $this->redirectToRoute('public_reminder_type', ['type'=>'no_longer']);
    }

    /**
     * @param $siteId
     * @param $type
     * @return RedirectResponse
     *
     * @Route("/reminder/no_longer/emergency_contact/{siteId}/{type}", name="public_reminder_no_longer_emergency_contact", methods={"GET"})
     */
    public function noLongerEmergencyContactAction($siteId, $type){
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository(Site::class)->find($siteId);
        if($site instanceof Site){
            if($type == 'primary'){
                $site->setPrimaryEmergencyContact(null);
                $em->persist($site);
                $em->flush();
            }
            elseif ($type == 'secondary'){
                $site->setSecondaryEmergencyContact(null);
                $em->persist($site);
                $em->flush();
            }
        }

        return $this->redirectToRoute('public_reminder_type', ['type'=>'no_longer']);
    }

}
