<?php

namespace App\Controller;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\MakerBundle\Maker\MakeRegistrationForm;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Participant;

class MainController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $loginForm = new Participant();
        $form = $this->createForm(RegistrationFormType::class, $loginForm);

        return $this->render('main/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'RegistrationForm' => $form->createView(),
        ]);

    }

    /**
     * @Route("/", name="accueil")
     */
    public function accueil(EntityManagerInterface $entityManager)
    {
        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();
        return $this->render('main/accueil.html.twig', compact('sites'));

    }



}
