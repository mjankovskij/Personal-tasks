<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {

        $user = new User();
        $formRegistration = $this->createForm(RegistrationFormType::class, $user);
        $formRegistration->handleRequest($request);
        $globalArray['registrationForm'] = $formRegistration->createView();

        $formLogin = $this->createForm(LoginFormType::class, $user);
        $formLogin->handleRequest($request);
        $globalArray['loginForm'] = $formLogin->createView();

        return $this->render('pages/index.html.twig', $globalArray);
    }
}
