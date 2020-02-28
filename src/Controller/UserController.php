<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\User;
use App\Entity\UserPermission;
use App\Form\Type\AdminType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller
 *
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="user_list", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->getAll($request->query->all());

        return $this->render('user/index.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'users',
            'users' => $users,
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param UserManagerInterface $userManager
     * @return RedirectResponse|Response
     *
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function createAction(Request $request, UserPasswordEncoderInterface $encoder, UserManagerInterface $userManager, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();

        $user = $userManager->createUser();
        $permissions = $em->getRepository(Permission::class)->findAll();

        $formUser = $this->createForm(AdminType::class, $user);
        $formUser->setData($user);

        $formUser->handleRequest($request);

        if ($formUser->isSubmitted()) {
            if($formUser->isValid()){
                $user->setUsername($user->getEmail());
                $password = substr(md5(uniqid(mt_rand(), true)) , 0, 8);;
                $passwordEncode = $encoder->encodePassword($user, $password);
                $user->setPassword($passwordEncode);
                if($request->request->has('role') && $request->request->get('role') == 'ROLE_SUPER_ADMIN'){
                    $user->removeRole('ROLE_ADMIN');
                    $user->addRole('ROLE_SUPER_ADMIN');
                }
                else{
                    $user->removeRole('ROLE_SUPER_ADMIN');
                    $user->addRole('ROLE_ADMIN');
                }

                $userManager->updateUser($user);

                $em->getRepository(UserPermission::class)->removePermissionsByUser($user);
                if($request->request->has('permissions') && !empty($request->request->get('permissions'))){
                    foreach ($request->request->get('permissions') as $permissionId=>$permissionValue){
                        $permission = $em->getRepository(Permission::class)->find($permissionId);
                        if($permission instanceof Permission){
                            $userPermission = $em->getRepository(UserPermission::class)->findOneBy(['user'=>$user, 'permission'=>$permission]);
                            if($userPermission instanceof UserPermission){
                                $userPermission->setViewable((isset($permissionValue['view']) && $permissionValue['view'] == true) ? true : false);
                                $userPermission->setAdded((isset($permissionValue['add']) && $permissionValue['add'] == true) ? true : false);
                                $userPermission->setEditable((isset($permissionValue['edit']) && $permissionValue['edit'] == true) ? true : false);
                                $userPermission->setRemovable((isset($permissionValue['remove']) && $permissionValue['remove'] == true) ? true : false);
                            }
                            else{
                                $userPermission = new UserPermission(
                                    $user,
                                    $permission,
                                    (isset($permissionValue['view']) && $permissionValue['view'] == true) ? true : false,
                                    (isset($permissionValue['add']) && $permissionValue['add'] == true) ? true : false,
                                    (isset($permissionValue['edit']) && $permissionValue['edit'] == true) ? true : false,
                                    (isset($permissionValue['remove']) && $permissionValue['remove'] == true) ? true : false
                                );
                            }
                            $em->persist($userPermission);
                            $em->flush();
                        }
                    }
                }

                $message = (new \Swift_Message('Your COLEASE Login Details'))
                    ->setFrom($_ENV['MAILER_FROM'], $_ENV['MAILER_FROM_NAME'])
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView('emails/create_user.html.twig', [
                            'firstName' => $user->getFirstName(),
                            'email' => $user->getEmail(),
                            'password' => $password,
                            'link' => $request->getSchemeAndHttpHost()."/login"
                        ]),
                        'text/html'
                    );
                try{
                    $mailer->send($message);
                }
                catch (\Exception $e){}


                $this->addFlash('success', 'User has been created!');
                return $this->redirectToRoute('user_list');
            }
            else{
                foreach($formUser->getErrors(true) as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        $userPermissions = $request->request->get('permissions');
        $role = $request->request->get('role');

        return $this->render('user/create.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'users',
            'formUser' => $formUser->createView(),
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
            'role' => $role,
        ]);
    }

    /**
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @param $id
     * @return RedirectResponse|Response
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, name="user_edit", methods={"GET", "POST"})
     */
    public function editByIdAction(Request $request, UserManagerInterface $userManager, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        if(!$user instanceof User){
            return $this->redirectToRoute('user_list');
        }
        $permissions = $em->getRepository(Permission::class)->findAll();

        $form = $this->createForm(AdminType::class, $user);
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()){
                $user->setUsername($user->getEmail());
                if($request->request->has('role') && $request->request->get('role') == 'ROLE_SUPER_ADMIN'){
                    $user->removeRole('ROLE_ADMIN');
                    $user->addRole('ROLE_SUPER_ADMIN');
                }
                else{
                    $user->removeRole('ROLE_SUPER_ADMIN');
                    $user->addRole('ROLE_ADMIN');
                }
                $userManager->updateUser($user);
                $em->getRepository(UserPermission::class)->removePermissionsByUser($user);
                if($request->request->has('permissions') && !empty($request->request->get('permissions'))){
                    foreach ($request->request->get('permissions') as $permissionId=>$permissionValue){
                        $permission = $em->getRepository(Permission::class)->find($permissionId);
                        if($permission instanceof Permission){
                            $userPermission = $em->getRepository(UserPermission::class)->findOneBy(['user'=>$user, 'permission'=>$permission]);
                            if($userPermission instanceof UserPermission){
                                $userPermission->setViewable((isset($permissionValue['view']) && $permissionValue['view'] == true) ? true : false);
                                $userPermission->setAdded((isset($permissionValue['add']) && $permissionValue['add'] == true) ? true : false);
                                $userPermission->setEditable((isset($permissionValue['edit']) && $permissionValue['edit'] == true) ? true : false);
                                $userPermission->setRemovable((isset($permissionValue['remove']) && $permissionValue['remove'] == true) ? true : false);
                            }
                            else{
                                $userPermission = new UserPermission(
                                    $user,
                                    $permission,
                                    (isset($permissionValue['view']) && $permissionValue['view'] == true) ? true : false,
                                    (isset($permissionValue['add']) && $permissionValue['add'] == true) ? true : false,
                                    (isset($permissionValue['edit']) && $permissionValue['edit'] == true) ? true : false,
                                    (isset($permissionValue['remove']) && $permissionValue['remove'] == true) ? true : false
                                );
                            }
                            $em->persist($userPermission);
                            $em->flush();

                        }
                    }
                }
                $this->addFlash('success', 'User has been updated!');
            }
            else{
                foreach($form->getErrors(true) as $error){
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        $userPermissionsResult = $em->getRepository(UserPermission::class)->findBy(['user'=>$user]);
        $userPermissions = [];
        foreach ($userPermissionsResult as $item){
            if($item instanceof UserPermission){
                $userPermissions[$item->getPermission()->getId()] = [
                    'view' => $item->getViewable(),
                    'add' => $item->getAdded(),
                    'edit' => $item->getEditable(),
                    'remove' => $item->getRemovable(),
                ];
            }
        }

        $role = $user->isSuperAdmin() ? 'ROLE_SUPER_ADMIN' : 'ROLE_ADMIN';

        return $this->render('user/edit.html.twig', [
            'active_menu1' => 'settings',
            'active_menu2' => 'users',
            'formUser' => $form->createView(),
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
            'role' => $role
        ]);
    }

    /**
     * @param UserManagerInterface $userManager
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"}, name="user_remove", methods={"GET"})
     */
    public function removeByIdAction(UserManagerInterface $userManager, $id){
        $user = $userManager->findUserBy(['id'=>$id]);
        if($user instanceof User){
            $userManager->deleteUser($user);
            $this->addFlash('success', 'User has been removed!');
        }

        return $this->redirectToRoute('user_list');
    }
}
