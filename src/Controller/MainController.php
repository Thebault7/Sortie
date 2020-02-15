<?php

namespace App\Controller;


use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Participant;
use App\Entity\Etat;
use App\Form\AccueilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

        return $this->render(
            'main/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    /**
     * @Route("/", name="accueil")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return
     */
    public function accueil(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        // on recherche en base de données les sorties qui sont actuellement ouvertes et qui
        // correspondent au site de l'utilisateur
        $etatRepository = $entityManager->getRepository(Etat::class);
        $etat = $etatRepository->findOneBy(['libelle' => 'Ouvert']);
        $sortieRepository = $entityManager->getRepository(Sortie::class);

        if ($request->query->get('id_site') === null) {
            $sorties = $sortieRepository->findBySiteAndEtat($user->getSite(), $etat);
        } else {
            $sorties = $sortieRepository->findBySiteAndEtat($request->query->get('id_site'), $etat);

            if ($sorties === []) {
                $this->addFlash("echec", "aucune ville correspondant aux critères de recherche n'a été trouvée.");
            }

            return $this->render('main/tableauAccueil.html.twig', compact('sites', 'sorties', 'user'));
        }

        return $this->render('main/accueil.html.twig', compact('sites', 'sorties', 'user'));
    }

    /**
     * @Route("/recherche", name="recherche")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return
     */
    public function recherche(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorttties = $sortieRepository->findAll();

//        require('organisateurFilter.php');

        $sorties = array_filter($sorttties, "organisateur");

//        if(isset($_POST['organisateur'])) {
//            $sorties = $sortieRepository->findBy(['participant' => $user->getId()]);
//        }

        return $this->render('main/accueil.html.twig', compact('sites', 'sorties', 'user'));
    }

    /**
     * @Route("/test", name="test")
    */
    public function test(){
        return $this->render('index.html.twig');
    }


}
