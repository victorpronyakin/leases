<?php


namespace App\Controller\Security;

use \FOS\UserBundle\Controller\ResettingController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Form\Type\ResettingFormType;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ResettingController
 * @package App\Controller\Security
 *
 * @Route("/resetting")
 */
class ResettingController extends BaseController
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var int
     */
    private $retryTtl;

    /**
     * ResettingController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserManagerInterface $userManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param \Swift_Mailer $mailer
     * @param AuthorizationCheckerInterface $authChecker
     * @param int $retryTtl
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, UserManagerInterface $userManager, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer, AuthorizationCheckerInterface $authChecker, $retryTtl = 7200) {
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
        $this->authChecker = $authChecker;
        $this->retryTtl = $retryTtl;
    }


    /**
     * Request reset user password: show form.
     *
     * @Route("/request", methods={"GET"})
     */
    public function requestAction()
    {
        if($this->authChecker->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('dashboard');
        }

        $error = (isset($_GET['error'])) ? $_GET['error'] : null;

        return $this->render('@FOSUser/Resetting/request.html.twig',[
            'error' => $error
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response|null
     * @throws \Exception
     *
     * @Route("/send-email", methods={"POST"})
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        $user = $this->userManager->findUserByUsernameOrEmail($username);

        $event = new GetResponseNullableUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null !== $user ) {
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $url = $this->generateUrl('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
            $message = (new \Swift_Message('Reset Your Colease Password'))
                ->setFrom($_ENV['MAILER_FROM'], $_ENV['MAILER_FROM_NAME'])
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView('emails/forgot_password.html.twig', [
                        'firstName' => $user->getFirstName(),
                        'link' => $url,
                        'notInfo' => true
                    ]),
                    'text/html'
                );
            $this->mailer->send($message);

            $user->setPasswordRequestedAt(new \DateTime());
            $this->userManager->updateUser($user);

            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email', array('username' => $username)));
        }
        else{
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request', array('error' => 'Invalid email address')));
        }

    }

    /**
     * @param Request $request
     * @param string $token
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
     *
     * @Route("/reset/{token}")
     */
    public function resetAction(Request $request, $token)
    {
        if($this->authChecker->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('dashboard');
        }

        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(ResettingFormType::class);
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $this->userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('dashboard');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(
                FOSUserEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            return $response;
        }

        return $this->render('@FOSUser/Resetting/reset.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }
}
