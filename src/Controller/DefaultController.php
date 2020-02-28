<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @return RedirectResponse
     *
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->redirectToRoute('dashboard');
    }
}
