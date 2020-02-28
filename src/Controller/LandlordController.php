<?php

namespace App\Controller;

use App\Entity\BEEStatusType;
use App\Entity\Country;
use App\Entity\Landlord;
use App\Entity\LandlordComments;
use App\Entity\LandlordContact;
use App\Entity\LandlordContactType;
use App\Entity\LandlordDocument;
use App\Entity\LandlordDocumentStatus;
use App\Entity\LandlordDocumentType;
use App\Entity\LandlordType;
use App\Entity\Lease;
use App\Helper\LeaseHelper;
use App\Helper\UserHelper;
use App\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class LandlordController
 * @package App\Controller
 *
 * @Route("/landlord")
 */
class LandlordController extends AbstractController
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
     *
     * @Route("/", name="landlord_list", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $landlordsResult = $em->getRepository(Landlord::class)->findAll();
        $landlords = [];
        foreach ($landlordsResult as $landlord){
            if($landlord instanceof Landlord){
                $contact = $em->getRepository(LandlordContact::class)->findOneBy(['landlord'=>$landlord]);
                $landlords[] = [
                    'id' => $landlord->getId(),
                    'name' => $landlord->getName(),
                    'typeName' => ($landlord->getType() instanceof LandlordType) ? $landlord->getType()->getName() : $landlord->getType(),
                    'address1' => $landlord->getAddress1(),
                    'documentStatus' => ($landlord->getDocumentStatus() instanceof LandlordDocumentStatus) ? $landlord->getDocumentStatus()->getName() : $landlord->getDocumentStatus(),
                    'contact' => $contact
                ];
            }
        }

        $landlordDocumentStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();


        return $this->render('sites_and_leases/landlord/index.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'landlords',
            'landlords' => $landlords,
            'landlordDocumentStatuses' => $landlordDocumentStatuses,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Landlord')
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/add", name="landlord_add", methods={"GET", "POST"})
     */
    public function addAction(Request $request, ValidatorInterface $validator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'added')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $landlord = new Landlord();
        $landlordDocuments = $em->getRepository(LandlordDocument::class)->findBy(['landlord'=>$landlord]);
        $landlordContacts = $em->getRepository(LandlordContact::class)->findBy(['landlord'=>$landlord]);
        $landlordComments = $em->getRepository(LandlordComments::class)->findBy(['landlord'=>$landlord]);


        if($request->request->has('submit')){
            $errorStatus = false;

            //BASIC INFO AND BANK
            $landlord->update($request->request->get('landlord'));
            if(!$landlord->getType() instanceof LandlordType){
                $landlordType = $em->getRepository(LandlordType::class)->find($landlord->getType());
                if($landlordType instanceof LandlordType){
                    $landlord->setType($landlordType);
                }
            }
            if(!$landlord->getBeeStatus() instanceof BEEStatusType){
                $beeStatus = $em->getRepository(BEEStatusType::class)->find($landlord->getBeeStatus());
                if($beeStatus instanceof BEEStatusType){
                    $landlord->setBeeStatus($beeStatus);
                }
            }
            if(!$landlord->getDocumentStatus() instanceof LandlordDocumentStatus){
                $documentStatus = $em->getRepository(LandlordDocumentStatus::class)->find($landlord->getDocumentStatus());
                if($documentStatus instanceof LandlordDocumentStatus){
                    $landlord->setDocumentStatus($documentStatus);
                }
            }
            //Upload bank proof
            if($request->files->has('landlord') && !empty($request->files->get('landlord'))){
                $landlordFile = $request->files->get('landlord');
                if(isset($landlordFile['bankDocument']) && $landlordFile['bankDocument'] instanceof UploadedFile){
                    $fileUpload = $landlordFile['bankDocument'];
                    $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                    $path = "upload/landlord/documents";
                    $filePath = $request->getSchemeAndHttpHost()."/".$path."/".$fileName;
                    try {
                        $fileUpload->move($path,$fileName);
                        $fileArray = [
                            'name' => $fileUpload->getClientOriginalName(),
                            'path' => $filePath,
                            'created' => new \DateTime(),
                        ];
                        $landlord->setBankDocument($fileArray);
                    } catch (\Exception $e) {
                        $this->addFlash('error', $e->getMessage());
                        $errorStatus = true;
                    }

                }
            }
            //Validation landlord
            $errors = $validator->validate($landlord);
            if(count($errors) === 0){
                $em->persist($landlord);
            }
            else {
                $errorStatus = true;
                foreach ($errors as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }
            //COMMENTS
            $landlordComments = [];
            if($request->request->has('comments') && !empty($request->request->get('comments'))){
                foreach ($request->request->get('comments') as $comment){
                    if(isset($comment['comment']) && !empty($comment['comment'])){
                        $landlordComment = null;
                        if(isset($comment['id']) && !empty($comment['id'])){
                            $landlordComment = $em->getRepository(LandlordComments::class)->find($comment['id']);
                        }
                        if($landlordComment instanceof LandlordComments){
                            if($landlordComment->getComment() != $comment['comment']){
                                $landlordComment->setComment($comment['comment']);
                                $landlordComment->setUpdated(new \DateTime());
                            }
                        }
                        else{
                            $landlordComment = new LandlordComments($landlord, $comment['comment']);
                        }
                        $em->persist($landlordComment);

                        $landlordComments[] = $landlordComment;
                    }
                    else{
                        $this->addFlash('error', 'Comment text is required');
                        $errorStatus = true;
                    }
                }
            }

            //DOCUMENTS
            $landlordDocuments = [];
            if($request->request->has('document') && !empty($request->request->get('document')) && !empty($request->files->get('document'))){
                $documentFiles = $request->files->get('document');
                foreach ($request->request->get('document') as $key => $documentItem){
                    $landlordDocumentType = $em->getRepository(LandlordDocumentType::class)->find($documentItem['type']);
                    if($landlordDocumentType instanceof LandlordDocumentType){
                        $landlordDocument = new LandlordDocument($landlord, $landlordDocumentType, null, null);
                        $filePath = null;
                        $originalName = null;
                        if(isset($documentFiles[$key]['document']) && $documentFiles[$key]['document'] instanceof UploadedFile) {
                            $fileUpload = $documentFiles[$key]['document'];
                            $originalName = $fileUpload->getClientOriginalName();
                            $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                            $path = "upload/landlord/documents";
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
                            $landlordDocument->setDocument($filePath);
                            if(!empty($originalName)){
                                $landlordDocument->setName($originalName);
                            }
                            $em->persist($landlordDocument);
                        }
                        else{
                            $this->addFlash('error', 'Document file is required');
                            $errorStatus = true;
                        }
                        $landlordDocuments[] = $landlordDocument;
                    }
                    else{
                        $this->addFlash('error', 'Document Type is required');
                        $errorStatus = true;
                    }
                }
            }
            //CONTACTS
            $landlordContacts=[];
            if($request->request->has('contact') && !empty($request->request->get('contact'))){
                foreach ($request->request->get('contact') as $key=>$contactItem){
                    $landlordContactType = $em->getRepository(LandlordContactType::class)->find($contactItem['type']);
                    if($landlordContactType instanceof LandlordContactType){
                        $landlordContact = new LandlordContact($landlord, $landlordContactType, $contactItem);
                        //Validation landlord
                        $errors = $validator->validate($landlordContact);
                        if(count($errors) === 0){
                            $em->persist($landlordContact);
                        }
                        else {
                            $errorStatus = true;
                            foreach ($errors as $error){
                                $this->addFlash('error', $error->getMessage());
                            }
                        }
                        $landlordContacts[] = $landlordContact;
                    }
                    else{
                        $this->addFlash('error', 'Contact Type is required');
                        $errorStatus = true;
                    }
                }
            }

            if($errorStatus == false){
                $em->flush();
                $this->addFlash('success', 'Landlord has been added!');
                return $this->redirectToRoute('landlord_list');
            }

        }

        $landlordTypes = $em->getRepository(LandlordType::class)->findAll();
        $beeStatuses = $em->getRepository(BEEStatusType::class)->findAll();
        $documentTypes = $em->getRepository(LandlordDocumentType::class)->findAll();
        $documentStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();
        $contactTypes = $em->getRepository(LandlordContactType::class)->findAll();

        return $this->render('sites_and_leases/landlord/add.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'landlords',
            'landlordTypes' => $landlordTypes,
            'beeStatuses' => $beeStatuses,
            'documentTypes' => $documentTypes,
            'documentStatuses' => $documentStatuses,
            'contactTypes' => $contactTypes,
            'landlord' => $landlord,
            'landlordDocuments' => $landlordDocuments,
            'landlordContacts' => $landlordContacts,
            'landlordComments' => $landlordComments,
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/view/{id}", name="landlord_view", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function viewByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'viewable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $landlord = $em->getRepository(Landlord::class)->find($id);
        if(!$landlord instanceof Landlord){
            $this->addFlash('error', 'Landlord not found');
            return $this->redirectToRoute('landlord_list');
        }


        $landlordDocuments = $em->getRepository(LandlordDocument::class)->findBy(['landlord'=>$landlord]);
        $landlordContacts = $em->getRepository(LandlordContact::class)->findBy(['landlord'=>$landlord]);
        $landlordComments = $em->getRepository(LandlordComments::class)->findBy(['landlord'=>$landlord]);

        $leasesResult = $em->getRepository(Lease::class)->findBy(['landlord'=>$landlord]);
        $leases = [];
        foreach ($leasesResult as $lease){
            if($lease instanceof Lease){
                $leases[] = [
                    'id' => $lease->getId(),
                    'site' => $lease->getSite(),
                    'startDate' => $lease->getStartDate(),
                    'endDate' => $lease->getEndDate(),
                    'currentTotalCost' => LeaseHelper::getCurrentTotalMonthlyCost($em, $lease),
                    'escalation' => LeaseHelper::getLeaseRentalPercentage($em, $lease),
                ];
            }
        }

        return $this->render('sites_and_leases/landlord/view.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'landlords',
            'landlord' => $landlord,
            'landlordDocuments' => $landlordDocuments,
            'landlordContacts' => $landlordContacts,
            'landlordComments' => $landlordComments,
            'leases' => $leases,
            'currencySymbol' => $this->settingService->getCurrencySymbol(),
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Landlord'),
            'userPermissionLease' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Lease'),
        ]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     *
     * @Route("/edit/{id}", name="landlord_edit", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function editByIdAction(Request $request, ValidatorInterface $validator, $id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'viewable') && !UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('dashboard');
        }
        $landlord = $em->getRepository(Landlord::class)->find($id);
        if(!$landlord instanceof Landlord){
            $this->addFlash('error', 'Landlord not found');
            return $this->redirectToRoute('landlord_list');
        }

        //STEP1
        if($request->request->has('submit_1')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'editable')) {
                $landlordData = $request->request->get('landlord');
                $landlord->update($landlordData);
                if(!$landlord->getType() instanceof LandlordType){
                    $landlordType = $em->getRepository(LandlordType::class)->find($landlord->getType());
                    if($landlordType instanceof LandlordType){
                        $landlord->setType($landlordType);
                    }
                }
                if(!$landlord->getBeeStatus() instanceof BEEStatusType){
                    $beeStatus = $em->getRepository(BEEStatusType::class)->find($landlord->getBeeStatus());
                    if($beeStatus instanceof BEEStatusType){
                        $landlord->setBeeStatus($beeStatus);
                    }
                }
                if (array_key_exists('vatStatus', $landlordData)) {
                    $landlord->setVatStatus(true);
                } else {
                    $landlord->setVatStatus(false);
                }
                //Upload bank proof
                if ($request->files->has('landlord') && !empty($request->files->get('landlord'))) {
                    $landlordFile = $request->files->get('landlord');
                    if (isset($landlordFile['bankDocument']) && $landlordFile['bankDocument'] instanceof UploadedFile) {
                        $fileUpload = $landlordFile['bankDocument'];
                        $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                        $path = "upload/landlord/documents";
                        $filePath = $request->getSchemeAndHttpHost()."/".$path."/".$fileName;
                        try {
                            $fileUpload->move($path, $fileName);
                            $fileArray = [
                                'name' => $fileUpload->getClientOriginalName(),
                                'path' => $filePath,
                                'created' => new \DateTime(),
                            ];
                            $landlord->setBankDocument($fileArray);
                        } catch (\Exception $e) {
                            $this->addFlash('error', $e->getMessage());
                        }
                    } else {
                        if (!isset($landlordData['bankDocument']) || empty($landlordData['bankDocument'])) {
                            $landlord->setBankDocument(null);
                        }
                    }
                } else {
                    if (!isset($landlordData['bankDocument']) || empty($landlordData['bankDocument'])) {
                        $landlord->setBankDocument(null);
                    }
                }

                $errors = $validator->validate($landlord);
                if(count($errors) === 0){
                    $em->persist($landlord);

                    $errorStatus = false;
                    $useID = [];
                    if ($request->request->has('comments') && !empty($request->request->get('comments'))) {
                        foreach ($request->request->get('comments') as $comment) {
                            if (isset($comment['comment']) && !empty($comment['comment'])) {
                                $landlordComment = null;
                                if (isset($comment['id']) && !empty($comment['id'])) {
                                    $landlordComment = $em->getRepository(LandlordComments::class)->find($comment['id']);
                                }
                                if ($landlordComment instanceof LandlordComments) {
                                    if ($landlordComment->getComment() != $comment['comment']) {
                                        $landlordComment->setComment($comment['comment']);
                                        $landlordComment->setUpdated(new \DateTime());
                                    }
                                    $useID[] = $landlordComment->getId();
                                } else {
                                    $landlordComment = new LandlordComments($landlord, $comment['comment']);
                                }
                                $em->persist($landlordComment);
                            } else {
                                $this->addFlash('error', 'Comment text is required');
                                $errorStatus = true;
                            }
                        }
                    }

                    if ($errorStatus == false) {
                        $em->getRepository(LandlordComments::class)->removeByLandlordNotIn($landlord, $useID);

                        $em->flush();
                        $this->addFlash('success', 'Landlord has been updated!');
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

        //STEP2
        if($request->request->has('submit_2')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'editable')) {
                $documentStatusOld = $landlord->getDocumentStatus();
                //DOCUMENTS
                $landlord->update($request->request->get('landlord'));
                if (!$landlord->getDocumentStatus() instanceof LandlordDocumentStatus) {
                    $documentStatus = $em->getRepository(LandlordDocumentStatus::class)->find(
                        $landlord->getDocumentStatus()
                    );
                    if ($documentStatus instanceof LandlordDocumentStatus) {
                        $landlord->setDocumentStatus($documentStatus);
                    }
                }
                if($documentStatusOld instanceof LandlordDocumentStatus && $landlord->getDocumentStatus() instanceof LandlordDocumentStatus){
                    if($documentStatusOld->getId() != $landlord->getDocumentStatus()->getId() || empty($landlord->getDocumentStatusUpdated())){
                        $landlord->setDocumentStatusUpdated(new \DateTime());
                    }
                }
                else{
                    $landlord->setDocumentStatusUpdated(new \DateTime());
                }

                $errors = $validator->validate($landlord);
                if (count($errors) === 0) {
                    $em->persist($landlord);

                    $errorStatus = false;
                    $useID = [];
                    if ($request->request->has('document') && !empty(
                        $request->request->get(
                            'document'
                        )
                        ) && !empty($request->files->get('document'))) {
                        $documentFiles = $request->files->get('document');
                        foreach ($request->request->get('document') as $key => $documentItem) {
                            $landlordDocumentType = $em->getRepository(LandlordDocumentType::class)->find(
                                $documentItem['type']
                            );
                            if ($landlordDocumentType instanceof LandlordDocumentType) {
                                $landlordDocument = null;
                                if (isset($documentItem['id']) && !empty($documentItem['id'])) {
                                    $landlordDocument = $em->getRepository(LandlordDocument::class)->findOneBy(
                                        ['landlord' => $landlord, 'id' => $documentItem['id']]
                                    );
                                }
                                if ($landlordDocument instanceof LandlordDocument) {
                                    $useID[] = $landlordDocument->getId();
                                    $landlordDocument->setType($landlordDocumentType);
                                } else {
                                    $landlordDocument = new LandlordDocument(
                                        $landlord,
                                        $landlordDocumentType,
                                        null,
                                        null
                                    );
                                }
                                $filePath = null;
                                $originalName = null;
                                if (isset($documentFiles[$key]['document']) && $documentFiles[$key]['document'] instanceof UploadedFile) {
                                    $fileUpload = $documentFiles[$key]['document'];
                                    $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                                    $originalName = $fileUpload->getClientOriginalName();
                                    $path = "upload/landlord/documents";
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
                                    $landlordDocument->setDocument($filePath);
                                    if (!empty($originalName)) {
                                        $landlordDocument->setName($originalName);
                                    }
                                    $em->persist($landlordDocument);
                                } else {
                                    $this->addFlash('error', 'Document file is required');
                                    $errorStatus = true;
                                }
                            } else {
                                $this->addFlash('error', 'Document Type is required');
                                $errorStatus = true;
                            }
                        }
                    }

                    if ($errorStatus == false) {
                        $em->getRepository(LandlordDocument::class)->removeByLandlordInNot($landlord, $useID);

                        $em->flush();
                        $this->addFlash('success', 'Landlord has been updated!');
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
        //STEP3
        if($request->request->has('submit_3')){
            if(UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'editable')) {
                $errorStatus = false;
                $useID = [];
                if ($request->request->has('contact') && !empty($request->request->get('contact'))) {
                    foreach ($request->request->get('contact') as $key => $contactItem) {
                        $landlordContactType = $em->getRepository(LandlordContactType::class)->find(
                            $contactItem['type']
                        );
                        if ($landlordContactType instanceof LandlordContactType) {
                            $landlordContact = null;
                            if (isset($contactItem['id']) && !empty($contactItem['id'])) {
                                $landlordContact = $em->getRepository(LandlordContact::class)->findOneBy(
                                    ['landlord' => $landlord, 'id' => $contactItem['id']]
                                );
                            }
                            if ($landlordContact instanceof LandlordContact) {
                                $landlordContact->update($landlordContactType, $contactItem);
                                $useID[] = $landlordContact->getId();
                            } else {
                                $landlordContact = new LandlordContact($landlord, $landlordContactType, $contactItem);
                            }
                            //Validation landlord
                            $errors = $validator->validate($landlordContact);
                            if (count($errors) === 0) {
                                $em->persist($landlordContact);
                            } else {
                                $errorStatus = true;
                                foreach ($errors as $error) {
                                    $this->addFlash('error', $error->getMessage());
                                }
                            }
                        } else {
                            $this->addFlash('error', 'Contact Type is required');
                            $errorStatus = true;
                        }
                    }
                }

                if ($errorStatus == false) {
                    $em->getRepository(LandlordContact::class)->removeByLandlordNotIn($landlord, $useID);

                    $em->flush();
                    $this->addFlash('success', 'Landlord has been updated!');
                }
            }
            else{
                $this->addFlash('error', "You don't have permission!");
            }
        }

        $landlordTypes = $em->getRepository(LandlordType::class)->findAll();
        $beeStatuses = $em->getRepository(BEEStatusType::class)->findAll();
        $documentTypes = $em->getRepository(LandlordDocumentType::class)->findAll();
        $documentStatuses = $em->getRepository(LandlordDocumentStatus::class)->findAll();
        $contactTypes = $em->getRepository(LandlordContactType::class)->findAll();
        $landlordDocuments = $em->getRepository(LandlordDocument::class)->findBy(['landlord'=>$landlord]);
        $landlordContacts = $em->getRepository(LandlordContact::class)->findBy(['landlord'=>$landlord]);
        $landlordComments = $em->getRepository(LandlordComments::class)->findBy(['landlord'=>$landlord]);


        return $this->render('sites_and_leases/landlord/edit.html.twig', [
            'active_menu1' => 'sites_leases',
            'active_menu2' => 'landlords',
            'landlordTypes' => $landlordTypes,
            'beeStatuses' => $beeStatuses,
            'documentTypes' => $documentTypes,
            'documentStatuses' => $documentStatuses,
            'contactTypes' => $contactTypes,
            'landlord' => $landlord,
            'landlordDocuments' => $landlordDocuments,
            'landlordContacts' => $landlordContacts,
            'landlordComments' => $landlordComments,
            'userPermission' => UserHelper::getUserPermissionByName($em, $this->getUser(), 'Landlord'),
        ]);
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", name="landlord_remove", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function removeByIdAction($id){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'removable')) {
            $this->addFlash('error', "You don't have permission!");

            return $this->redirectToRoute('landlord_list');
        }
        $landlord = $em->getRepository(Landlord::class)->find($id);
        if($landlord instanceof Landlord){
            $em->remove($landlord);
            $em->flush();

            $this->addFlash('success', 'Landlord has been removed');
        }
        else{
            $this->addFlash('error', 'Landlord not found');
        }

        return $this->redirectToRoute('landlord_list');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/createContactType", name="contact_type_create", methods={"POST"})
     */
    public function createContactTypeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        if($request->request->has('name') && !empty($request->request->get('name'))){
            $contactType = $em->getRepository(LandlordContactType::class)->findOneBy(['name'=>$request->request->get('name')]);
            if(!$contactType instanceof LandlordContactType){
                $contactType = new LandlordContactType($request->request->get('name'));
                $em->persist($contactType);
                $em->flush();
            }

            return new JsonResponse(['result'=>true, 'id'=>$contactType->getId(), 'name'=>$contactType->getName()]);
        }

        return new JsonResponse(['result'=>false]);
    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     *
     * @Route("/createContact", name="contact_create", methods={"POST"})
     */
    public function createContactAction(Request $request, ValidatorInterface $validator){
        $em = $this->getDoctrine()->getManager();
        if(!UserHelper::checkPermission($em, $this->getUser(), 'Landlord', 'editable')) {
            $this->addFlash('error', "You don't have permission!");

            return new JsonResponse(['result'=>false, 'message'=>"You don't have permission!"]);
        }
        if($request->request->has('landlord') && !empty($request->request->get('landlord'))){
            $landlord = $em->getRepository(Landlord::class)->find($request->request->get('landlord'));
            if($landlord instanceof Landlord){
                if($request->request->has('type') && !empty($request->request->get('type'))){
                    $landlordContactType = $em->getRepository(LandlordContactType::class)->find($request->request->get('type'));
                    if($landlordContactType instanceof LandlordContactType){
                        $landlordContact = new LandlordContact($landlord, $landlordContactType, $request->request->all());
                        $errors = $validator->validate($landlordContact);
                        if(count($errors) === 0){
                            $em->persist($landlordContact);
                            $em->flush();

                            return new JsonResponse([
                                'result'=>true,
                                'id'=>$landlordContact->getId(),
                                'name'=>$landlordContact->getLastName()." ".$landlordContact->getLastName()
                            ]);
                        }
                        else {
                            $message = [];
                            foreach ($errors as $error){
                                $message[] = $error->getMessage()."; ";
                            }
                            return new JsonResponse(['result'=>false, 'message'=>$message]);
                        }
                    }
                    else{
                        return new JsonResponse(['result'=>false, 'message'=>'Contact Type Not Found']);
                    }
                }
                else{
                    return new JsonResponse(['result'=>false, 'message'=>'Contact Type is required']);
                }

            }
            else{
                return new JsonResponse(['result'=>false, 'message'=>'Landlord Not Found']);
            }
        }

        return new JsonResponse(['result'=>false, 'message'=>'Landlord is required']);
    }
}
