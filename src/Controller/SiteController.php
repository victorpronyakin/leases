<?php

namespace App\Controller;

use App\Entity\HoursOfAccess;
use App\Entity\Landlord;
use App\Entity\LandlordContact;
use App\Entity\LandlordContactType;
use App\Entity\Lease;
use App\Entity\ManagementStatus;
use App\Entity\Site;
use App\Entity\SiteInventory;
use App\Entity\SiteInventoryCategory;
use App\Helper\UserHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SiteController
 * @package App\Controller
 *
 * @Route("/site")
 */
class SiteController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route("/", name="site_list", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Site', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }

        $sitesResult = $em->getRepository(Site::class)->findAll();
        $sites = [];
        foreach ($sitesResult as $site){
            if($site instanceof Site){
                $lease = $em->getRepository(Lease::class)->findBy(['site'=>$site],['id'=>'DESC']);
                $sites[] = [
                    'id' => $site->getId(),
                    'number' => $site->getNumber(),
                    'name' => $site->getName(),
                    'address' => $site->getAddress(),
                    'siteStatus' => $site->getSiteStatus(),
                    'primaryEmergencyContact' => $site->getPrimaryEmergencyContact(),
                    'leaseId' => (isset($lease[0]) && $lease[0] instanceof Lease) ? $lease[0]->getId() : null
                ];
            }
        }
        $siteStatuses = $em->getRepository(ManagementStatus::class)->findAll();

        return $this->render('sites_and_leases/site/index.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'sites',
            'sites' => $sites,
            'siteStatuses' => $siteStatuses,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Site')
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/add", name="site_add", methods={"GET", "POST"})
     */
    public function addAction(Request $request, ValidatorInterface $validator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Site', 'added')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $site = new Site();
        $siteInventories = $em->getRepository(SiteInventory::class)->findBy(['site'=>$site]);

        if($request->request->has('submit')){

            $errorStatus = false;
            $site->update($request->request->get('site'));
            if(!$site->getHoursOfAccess() instanceof HoursOfAccess && !empty($site->getHoursOfAccess())){
                $hoursOfAccess = $em->getRepository(HoursOfAccess::class)->find($site->getHoursOfAccess());
                if($hoursOfAccess instanceof HoursOfAccess){
                    $site->setHoursOfAccess($hoursOfAccess);
                }
            }
            if(!$site->getSiteStatus() instanceof ManagementStatus && !empty($site->getSiteStatus())){
                $siteStatus = $em->getRepository(ManagementStatus::class)->find($site->getSiteStatus());
                if($siteStatus instanceof ManagementStatus){
                    $site->setSiteStatus($siteStatus);
                }
            }
            if(!$site->getPrimaryEmergencyContact() instanceof LandlordContact && !empty($site->getPrimaryEmergencyContact())){
                $primaryEmergencyContact = $em->getRepository(LandlordContact::class)->find($site->getPrimaryEmergencyContact());
                if($primaryEmergencyContact instanceof LandlordContact){
                    $site->setPrimaryEmergencyContact($primaryEmergencyContact);
                }
            }
            if(!$site->getSecondaryEmergencyContact() instanceof LandlordContact && !empty($site->getSecondaryEmergencyContact())){
                $secondaryEmergencyContact = $em->getRepository(LandlordContact::class)->find($site->getSecondaryEmergencyContact());
                if($secondaryEmergencyContact instanceof LandlordContact){
                    $site->setSecondaryEmergencyContact($secondaryEmergencyContact);
                }
            }

            $errors = $validator->validate($site);
            if(count($errors) === 0){
                $em->persist($site);
            }
            else {
                $errorStatus = true;
                foreach ($errors as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }

            $siteInventories = [];
            if($request->request->has('siteInventory') && !empty($request->request->get('siteInventory'))){
                foreach ($request->request->get('siteInventory') as $siteInventoryItem){
                    $siteInventory = new SiteInventory($site, $siteInventoryItem);
                    if(!$siteInventory->getCategory() instanceof SiteInventoryCategory && !empty($siteInventory->getCategory())){
                        $siteInventoryCategory = $em->getRepository(SiteInventoryCategory::class)->find($siteInventory->getCategory());
                        if($siteInventoryCategory instanceof SiteInventoryCategory){
                            $siteInventory->setCategory($siteInventoryCategory);
                        }
                    }
                    $errors = $validator->validate($siteInventory);
                    if(count($errors) === 0){
                        $em->persist($siteInventory);
                    }
                    else {
                        $errorStatus = true;
                        foreach ($errors as $error){
                            $this->addFlash('error', $error->getMessage());
                        }
                    }
                    $siteInventories[] = $siteInventory;
                }
            }

            if($errorStatus == false){

                $em->flush();
                $this->addFlash('success', 'Site has been created');

                return $this->redirectToRoute('site_list');
            }
        }

        $hoursOfAccessTypes = $em->getRepository(HoursOfAccess::class)->findAll();
        $siteInventoryCategories = $em->getRepository(SiteInventoryCategory::class)->findAll();
        $landlords = $em->getRepository(Landlord::class)->findAll();
        $landlordContacts = $em->getRepository(LandlordContact::class)->findAll();
        $landlordContactTypes = $em->getRepository(LandlordContactType::class)->findAll();
        $sitesStatuses = $em->getRepository(ManagementStatus::class)->findAll();

        return $this->render('sites_and_leases/site/add.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'sites',
            'site' => $site,
            'hoursOfAccessTypes' => $hoursOfAccessTypes,
            'siteInventoryCategories' => $siteInventoryCategories,
            'siteInventories' => $siteInventories,
            'landlords' => $landlords,
            'landlordContacts' => $landlordContacts,
            'landlordContactTypes' => $landlordContactTypes,
            'sitesStatuses' => $sitesStatuses,
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/edit/{id}", name="site_edit", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function editByIdAction(Request $request, ValidatorInterface $validator, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Site', 'viewable') && !UserHelper::checkPermission($em, $this->getUser(), 'Site', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $site = $em->getRepository(Site::class)->find($id);
        if(!$site instanceof Site){
            $this->addFlash('error', 'Site not found');
            return $this->redirectToRoute('site_list');
        }

        if($request->request->has('submit')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Site', 'editable')) {
                $errorStatus = false;
                $oldSiteStatus = $site->getSiteStatus();
                $oldHoursOfAccess = $site->getHoursOfAccess();
                $oldOtherAccess = $site->getOtherAccess();
                $siteData = $request->request->get('site');
                $site->update($siteData);
                if(!$site->getHoursOfAccess() instanceof HoursOfAccess && !empty($site->getHoursOfAccess())){
                    $hoursOfAccess = $em->getRepository(HoursOfAccess::class)->find($site->getHoursOfAccess());
                    if($hoursOfAccess instanceof HoursOfAccess){
                        $site->setHoursOfAccess($hoursOfAccess);
                    }
                }
                if(!$site->getSiteStatus() instanceof ManagementStatus && !empty($site->getSiteStatus())){
                    $siteStatus = $em->getRepository(ManagementStatus::class)->find($site->getSiteStatus());
                    if($siteStatus instanceof ManagementStatus){
                        $site->setSiteStatus($siteStatus);
                        if(!$oldSiteStatus instanceof ManagementStatus || $oldSiteStatus->getId() != $site->getSiteStatus()->getId()){
                            $site->setSiteStatusUpdated(new \DateTime());
                        }
                    }
                }
                if(!$site->getPrimaryEmergencyContact() instanceof LandlordContact && !empty($site->getPrimaryEmergencyContact())){
                    $primaryEmergencyContact = $em->getRepository(LandlordContact::class)->find($site->getPrimaryEmergencyContact());
                    if($primaryEmergencyContact instanceof LandlordContact){
                        $site->setPrimaryEmergencyContact($primaryEmergencyContact);
                    }
                }
                if(!$site->getSecondaryEmergencyContact() instanceof LandlordContact && !empty($site->getSecondaryEmergencyContact())){
                    $secondaryEmergencyContact = $em->getRepository(LandlordContact::class)->find($site->getSecondaryEmergencyContact());
                    if($secondaryEmergencyContact instanceof LandlordContact){
                        $site->setSecondaryEmergencyContact($secondaryEmergencyContact);
                    }
                }

                if(!$request->request->has('emergencyAccessUpdatedManually')) {
                    if (
                        ($oldHoursOfAccess instanceof HoursOfAccess && $site->getHoursOfAccess(
                            ) instanceof HoursOfAccess
                            && $oldHoursOfAccess->getId() != $site->getHoursOfAccess()->getId())
                        || $oldOtherAccess != $site->getOtherAccess()
                    ) {
                        $site->setEmergencyAccessUpdated(new \DateTime());
                    }
                }

                $errors = $validator->validate($site);
                if(count($errors) === 0){
                    $em->persist($site);
                }
                else {
                    $errorStatus = true;
                    foreach ($errors as $error){
                        $this->addFlash('error', $error->getMessage());
                    }
                }

                $siteInventoryIds = [];
                if($request->request->has('siteInventory') && !empty($request->request->get('siteInventory'))){
                    foreach ($request->request->get('siteInventory') as $siteInventoryItem){
                        $siteInventory = null;
                        $updateStatus = false;
                        if(isset($siteInventoryItem['id']) && !empty($siteInventoryItem['id'])){
                            $siteInventory = $em->getRepository(SiteInventory::class)->findOneBy(['site'=>$site, 'id'=>$siteInventoryItem['id']]);
                        }
                        if($siteInventory instanceof SiteInventory){
                            if(isset($siteInventoryItem['category']) && $siteInventory->getCategory()->getId() != $siteInventoryItem['category']){
                                $updateStatus = true;
                            }
                            if(isset($siteInventoryItem['quantity']) && $siteInventory->getQuantity() != $siteInventoryItem['quantity']){
                                $updateStatus = true;
                            }
                            if(isset($siteInventoryItem['info']) && $siteInventory->getInfo() != $siteInventoryItem['info']){
                                $updateStatus = true;
                            }
                            $siteInventory->update($siteInventoryItem);
                            $siteInventoryIds[] = $siteInventory->getId();
                        }
                        else{
                            $siteInventory = new SiteInventory($site, $siteInventoryItem);
                        }
                        if(!$siteInventory->getCategory() instanceof SiteInventoryCategory && !empty($siteInventory->getCategory())){
                            $siteInventoryCategory = $em->getRepository(SiteInventoryCategory::class)->find($siteInventory->getCategory());
                            if($siteInventoryCategory instanceof SiteInventoryCategory){
                                $siteInventory->setCategory($siteInventoryCategory);
                            }
                        }
                        $errors = $validator->validate($siteInventory);
                        if(count($errors) === 0){
                            if($updateStatus){
                                $siteInventory->setUpdated(new \DateTime());
                            }
                            $em->persist($siteInventory);
                        }
                        else {
                            $errorStatus = true;
                            foreach ($errors as $error){
                                $this->addFlash('error', $error->getMessage());
                            }
                        }
                    }
                }

                if($errorStatus == false){
                    $em->getRepository(SiteInventory::class)->removeBySiteNotIn($site, $siteInventoryIds);

                    $em->flush();
                    $this->addFlash('success', 'Site has been updated');
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }

        $hoursOfAccessTypes = $em->getRepository(HoursOfAccess::class)->findAll();
        $siteInventoryCategories = $em->getRepository(SiteInventoryCategory::class)->findAll();
        $landlords = $em->getRepository(Landlord::class)->findAll();
        $landlordContacts = $em->getRepository(LandlordContact::class)->findAll();
        $landlordContactTypes = $em->getRepository(LandlordContactType::class)->findAll();
        $siteInventories = $em->getRepository(SiteInventory::class)->findBy(['site'=>$site]);
        $sitesStatuses = $em->getRepository(ManagementStatus::class)->findAll();


        return $this->render('sites_and_leases/site/edit.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'sites',
            'site' => $site,
            'hoursOfAccessTypes' => $hoursOfAccessTypes,
            'siteInventoryCategories' => $siteInventoryCategories,
            'siteInventories' => $siteInventories,
            'landlords' => $landlords,
            'landlordContacts' => $landlordContacts,
            'landlordContactTypes' => $landlordContactTypes,
            'sitesStatuses' => $sitesStatuses,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Site')
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", name="site_remove", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function removeByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Site', 'removable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('site_list');
        }
        $site = $em->getRepository(Site::class)->find($id);
        if($site instanceof Site){
            $em->remove($site);
            $em->flush();

            $this->addFlash('success', 'Site has been removed');
        }
        else{
            $this->addFlash('error', 'Site not found');
        }
        return $this->redirectToRoute('site_list');
    }

}
