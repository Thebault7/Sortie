<?php

namespace App\Controller;


use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Participant;
use App\Entity\Etat;
use App\Form\AccueilType;
use App\Services\OrganisateurFilter;
use App\Services\SortiesPassees;
use App\Services\SortiesInscrit;
use App\Services\ContientUser;
use App\Services\NomSortieFiltre;
use App\Services\DateDebutFiltre;
use App\Services\DateFinFiltre;
use App\Services\SiteFiltre;
use App\Services\GestionSorties\CloturerInscription;
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
     * @Route("/accueil", name="accueil")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return
     */
    public function accueil(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        // on recherche en base de données les sorties qui correspondent au site de l'utilisateur
        $sortieRepository = $entityManager->getRepository(Sortie::class);

        if ($request->query->get('id_site') === null) {
            $sorties = $sortieRepository->findBySite($user->getSite());
        } elseif ($request->query->get('id_site') === 'tous_sites') {
            $sorties = $sortieRepository->findAll();

            return $this->render('main/tableauAccueil.html.twig', compact('sites', 'sorties', 'user'));
        } else {
            $sorties = $sortieRepository->findBySite($request->query->get('id_site'));

            if ($sorties === []) {
                $this->addFlash("echec", "aucune sortie correspondant aux critères de recherche n'a été trouvée.");
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
        // Si l'utilisateur fait une recherche sur la page d'accueil, puis ensuite click 'entrer' sur
        // l'url 'http://localhost/sortie/public/recherche', il y a une erreur 500 qui est levée.
        // Pour l'éviter, on réoriente sur la page d'accueil sans la recherche:
        if (isset($_POST['nom_sortie']) === false) {
            return $this->redirect($this->generateUrl('accueil'));
        }

        // Cette fonction s'occupe de filtrer les sorties en fonction des filtres à appliquer.
        // Pour ce faire, on charge toutes les sorties de la base de données dans un tableau.
        // Ensuite, dans ce tableau chaque filtre remplace par 'false' les sorties qui ne
        // correspondent pas. On fait ça successivement pour chaque filtre. A la fin, la méthode
        // 'array_filter' de PHP se charge d'enlever tous les 'false' du tableau, ne laissant
        // que les sorties sélectionnées.

        $user = $this->getUser();

        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        if (isset($_POST['organisateur'])) {
            // Le service 'OrganisateurFilter' remplace par 'false' toutes les sorties dont
            // l'utilisateur connecté n'est pas l'organisateur.
            $organisateur = new OrganisateurFilter($sorties, $user);
            $sorties = $organisateur->organisateur();
        }

        if (isset($_POST['passees'])) {
            // Le service 'sortiesPassees' remplace par 'false' toutes les sorties qui ne sont pas
            // terminées.
            $sortiesPassees = new SortiesPassees($sorties);
            $sorties = $sortiesPassees->sortiesPassees();
        }

        if (isset($_POST['inscrit'])) {
            $sortiesInscrit = new SortiesInscrit($sorties, $user);
            if ($_POST['inscrit'] === 'inscrit') {
                // Le service 'sortiesInscrit' remplace par 'false' toutes les sorties où l'utilisateur
                // n'est pas inscrit.
                $sorties = $sortiesInscrit->sortiesInscrit();
            } else {
                // Autrement la méthode 'sortiesNonInscrit' remplace par 'false' toutes les sorties où
                // l'utilisateur est inscrit.
                $sorties = $sortiesInscrit->sortiesNonInscrit();
            }
        }

        if ($_POST['nom_sortie'] !== "") {
            // on filtre par le string écrit dans "le nom contient :"
            $nomSortieFiltre = new NomSortieFiltre($sorties, $_POST['nom_sortie']);
            $sorties = $nomSortieFiltre->nomSortieFiltre();
        }

        if ($_POST['date_debut'] !== "") {
            // on filtre par date de début
            $dateDebutFiltre = new DateDebutFiltre($sorties, $_POST['date_debut']);
            $sorties = $dateDebutFiltre->dateDebutFiltre();
        }

        if ($_POST['date_fin'] !== "") {
            // on filtre par date de fin
            $dateFinFiltre = new DateFinFiltre($sorties, $_POST['date_fin']);
            $sorties = $dateFinFiltre->dateFinFiltre();
        }

        if ($_POST['select_sites'] !== "tous_sites") {
            // on filtre sur les sites
            $siteFiltre = new SiteFiltre($sorties, $_POST['select_sites']);
            $sorties = $siteFiltre->siteFiltre();
        }

        $sorties = array_filter($sorties);

        return $this->render('main/accueil.html.twig', compact('sites', 'sorties', 'user'));
    }

    /**
     * @Route("/test", name="test")
    */
    public function test(){
        return $this->render('index.html.twig');
    }
}
