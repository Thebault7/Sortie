<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Participant;
use App\Entity\Etat;
use App\Form\AccueilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\MakerBundle\Maker\MakeRegistrationForm;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


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

        return $this->render('main/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/", name="accueil")
     * @param EntityManagerInterface $entityManager
     * @return
     */
    public function accueil(EntityManagerInterface $entityManager)
    {
        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        return $this->render('main/accueil.html.twig', compact('sites', 'sorties'));
    }
}
