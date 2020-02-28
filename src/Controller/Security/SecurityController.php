<?php


namespace App\Controller\Security;

use \FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends BaseController
{
    /**
     * @var CsrfTokenManagerInterface|null
     */
    protected $tokenManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * SecurityController constructor.
     * @param CsrfTokenManagerInterface|null $tokenManager
     * @param AuthorizationCheckerInterface $authChecker
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager = null, AuthorizationCheckerInterface $authChecker = null)
    {
        $this->tokenManager = $tokenManager;
        $this->authChecker = $authChecker;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/login", methods={"GET"})
     */
    public function loginAction(Request $request)
    {
        if($this->authChecker->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('dashboard');
        }
        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $csrfToken = $this->tokenManager
            ? $this->tokenManager->getToken('authenticate')->getValue()
            : null;

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }
}
