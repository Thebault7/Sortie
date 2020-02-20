<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Annulation;
use App\Entity\Lieu;
use App\Form\LieuType;
use App\Form\AnnulationType;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Constantes\EtatConstantes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;


/**
 * @Route("/sortie", name="sortie_")
 */
class SortieController extends Controller
{
    /**
     * @Route("/add", name="add")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request, EntityManagerInterface $entityManager)
    {
        $site = $this->getUser()->getSite();

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $etatRepository = $entityManager->getRepository(Etat::class);
            $etats = $etatRepository->findAll();
            for ($i = 0; $i < count($etats); $i++) {
                if ($etats[$i]->getLibelle() === EtatConstantes::CREE) {
                    $etatCree = $etats[$i];
                }
                if ($etats[$i]->getLibelle() === EtatConstantes::OUVERT) {
                    $etatOuvert = $etats[$i];
                }
            }

            $sortie
                ->setParticipant($this->getUser())
                ->setSite($site);

            if ($sortieForm->get('creer')->isClicked()) {
                $etat = $etatRepository->find($etatCree->getId());
                $sortie->setEtat($etat);
            } elseif ($sortieForm->get('publier')->isClicked()) {
                $etat = $etatRepository->find($etatOuvert->getId());
                $sortie->setEtat($etat);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Une nouvelle sortie a été ajoutée!');

            return $this->redirectToRoute("accueil");
        }

        return $this->render(
            'sortie/add.html.twig',
            ['sortieFormView' => $sortieForm->createView(), 'lieuFormView' => $lieuForm->createView(), 'site' => $site]
        );
    }

    /**
     * @Route("/afficher/{id}", name="afficher", requirements={"id": "\d+"})
     */
    public function afficherSortie($id, EntityManagerInterface $entityManager)
    {
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/afficher.html.twig', compact('sortie'));
    }

    /**
     * @Route("/inscription/{id}", name="inscription", requirements={"id": "\d+"})
     */
    public function sinscrire($id, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);
        $participants = $sortie->getParticipants();

        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => EtatConstantes::OUVERT]);

        // inscription impossible pour l'organisateur de la sortie
        if ($user->getId() === $sortie->getParticipant()->getId()) {
            $this->addFlash('warning', 'Echec inscription. Vous êtes l\'organisateur de cette sortie...');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        // si le participant essaie de s'incrire une 2eme fois a la sortie
        foreach ($participants as $participant) {
            if ($participant->getId() === $user->getId()) {
                $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie!');

                return $this->redirectToRoute('sortie_afficher', compact('id'));
            }
        }

        //si le nombre d'inscriptions max a été atteint
        $nbInscripMax = $sortie->getNbInscriptionMax();
        if (count($participants) >= $nbInscripMax) {
            $this->addFlash('danger', 'Le nombre maximal d\'inscriptions a dèjà été atteint!');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //si etat nest pas "ouvert"
        $etat = $sortie->getEtat();
        if ($etat->getLibelle() !== $etatOuvert->getLibelle()) {
            $this->addFlash(
                'danger',
                'Il n\'est pas possible de s\'inscrire à cette sortie. Il est uniquement possible de s\'inscrire aux sorties dont l\'état est égal à "ouvert". '
            );

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //si la date limite d'inscriptions est dépassée
        $dateLimiteInscriptions = $sortie->getDateLimiteInscription();
        if ($dateLimiteInscriptions <= new \DateTime('now')) {
            $this->addFlash('danger', 'La date limite d\'inscriptions a été dépassée.');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        // si, lors de l'inscription, le nombre maximum d'inscrits est atteint, il faut changer l'état
        // de la sortie de Ouvert à Clôturé
        if (count($sortie->getParticipants()) >= $sortie->getNbInscriptionMax() - 1) {
            $etatCloture = $etatRepository->findOneBy(['libelle' => EtatConstantes::CLOTURE]);
            $sortie->setEtat($etatCloture);
        }

        $sortie->addParticipant($user);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'Votre inscription a été prise en compte!');

        return $this->redirectToRoute('sortie_afficher', compact('id'));
    }

    /**
     * @Route("/desister/{id}", name="desister", requirements={"id": "\d+"})
     */
    public function desister($id, EntityManagerInterface $entityManager)
    {

        $user = $this->getUser();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => EtatConstantes::OUVERT]);
        $etatCloture = $etatRepository->findOneBy(['libelle' => EtatConstantes::CLOTURE]);

        $participants = $sortie->getParticipants();
        $etat = $sortie->getEtat();
        $dateDebutSortie = $sortie->getDateHeureDebut();

        //desistement impossible si je suis l'organisateur de la sortie
        if ($user->getId() === $sortie->getParticipant()->getId()) {
            $this->addFlash('warning', 'Echec désistement. Vous êtes l\'organisateur de cette sortie...');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //verifier si l'etat est egal a "ouvert" ou "cloture" --> sinon desistement n'est pas possible
        if ($etat->getLibelle() !== $etatOuvert->getLibelle() && $etat->getLibelle() !== $etatCloture->getLibelle()) {
            $this->addFlash('danger', 'L\'option \'désistement\' n\'est plus valide! ');
        } //si user n'est pas sur la liste des participants
        elseif (!($participants->contains($user))) {
            $this->addFlash('danger', 'Vous ne pouvez pas désister car vous ne participez pas à cette sortie!');
        } // verif si la sortie n'a pas déja débuté
        elseif ($dateDebutSortie <= new \DateTime('now')) {
            $this->addFlash('danger', 'Sortie est dèjà en cours! Impossible de désister!');
        } // verifier encore si le user est bien inscrit a cette sortie avant de le supprimer de la liste
        elseif ($participants->contains($user)) {
            // suppression de participant
            $sortie->removeParticipant($user);

            // on vérifie que la sortie d'où l'utilisateur s'est désisté était remplie. Si oui, une place vient de se libérer
            // et il faut donc la remettre ouverte.
            if ($sortie->getEtat()->getLibelle() === $etatCloture->getLibelle()) {
                $sortie->setEtat($etatOuvert);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre désistement a été pris en compte!');
        }

        return $this->redirectToRoute('sortie_afficher', compact('id'));
    }


    /**
     * @Route("/annuler/{id}", name="annuler", requirements={"id": "\d+"})
     */
    public function annuler($id, EntityManagerInterface $entityManager, Request $request)
    {

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        $annulation = new Annulation();
        $annulationForm = $this->createForm(AnnulationType::class, $annulation);

        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatAnnule = $etatRepository->findOneBy(['libelle' => EtatConstantes::ANNULE]);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => EtatConstantes::OUVERT]);
        $etatCloture = $etatRepository->findOneBy(['libelle' => EtatConstantes::CLOTURE]);

        // echec annulation si user n'est pas organisateur ou pas administrateur
        if ($this->getUser()->getId() !== $sortie->getParticipant()->getId() && $this->getUser()->getAdministrateur() === false) {
            $this->addFlash('danger', 'Vous ne pouvez pas annuler la sortie dont vous n\'êtes pas l\'organisateur!');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }
        // echec annulation si la date de debut de la sortie depassee
        if ($sortie->getDateHeureDebut() < new \DateTime()) {
            $this->addFlash('danger', "Impossible d'annuler la sortie dont la date de début a été déjà dépassée");

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        // echec annulation si l'etat n'est pas ouvert ou cloture
        $etatLibelle = $sortie->getEtat()->getLibelle();

        if ($etatLibelle === $etatAnnule->getLibelle()) {
            $this->addFlash('danger', "Impossible d'annuler une sortie déjà annulée !");

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        if ($etatLibelle !== $etatOuvert->getLibelle() && $etatLibelle !== $etatCloture->getLibelle()) {
            $this->addFlash(
                'danger',
                "Impossible d'annuler la sortie qui dont l'état n'est pas 'ouvert' ou 'clôturé'!"
            );

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        $annulationForm->handleRequest($request);

        if ($annulationForm->isSubmitted() && $annulationForm->isValid()) {

            $annulation->setSortie($sortie);
            $entityManager->persist($annulation);
            // set etat sortie a annule
            $etat = $entityManager->getRepository(Etat::class)->find($etatAnnule->getId());
            $sortie->setEtat($etat);
            $entityManager->flush();

            $this->addFlash('success', "La sortie a été annulée.");

            return $this->redirectToRoute('accueil');

        }

        return $this->render(
            'sortie/annuler.html.twig',
            ['sortie' => $sortie, 'annulationFormView' => $annulationForm->createView()]
        );
    }


    /**
     * @Route("/supprimer/{id}", name="supprimer", requirements={"id": "\d+"})
     */
    public function supprimer($id, EntityManagerInterface $entityManager)
    {

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        $etatLibelle = $sortie->getEtat()->getLibelle();

        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatCree = $etatRepository->findOneBy(['libelle' => EtatConstantes::CREE]);

        // echec suppresion si user n'est pas organisateur
        if ($this->getUser()->getId() !== $sortie->getParticipant()->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer la sortie dont vous n\'êtes pas l\'organisateur!');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        // possibilite de supprimer une sortie uniquement avec etat 'cree'
        if ($etatLibelle !== $etatCree->getLibelle()) {
            $this->addFlash('danger', "Impossible de supprimer une sortie déjà publiée!");
            $id = $sortie->getId();

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }
        $entityManager->remove($sortie);
        $entityManager->flush();

        $this->addFlash('success', "Sortie supprimée!");

        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/messorties", name="messorties")
     */
    public function lister(EntityManagerInterface $entityManager)
    {

        $idUser = $this->getUser()->getId();

        $sortiesOrganisateur = $entityManager->getRepository(Sortie::class)->findSortieDontJeSuisOrganisateur($idUser);
        $sortiesParticipant = $entityManager->getRepository(Sortie::class)->findSortieAuxquellesJeSuisInscrit($idUser);

        return $this->render('sortie/messorties.html.twig', compact('sortiesOrganisateur', 'sortiesParticipant'));
    }

    /**
     * @Route("/modifsortie/{id}", name="modifsortie", requirements={"id": "\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifsortie($id, Request $request, EntityManagerInterface $entityManager)
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatCree = $etatRepository->findOneBy(['libelle' => EtatConstantes::CREE]);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => EtatConstantes::OUVERT]);

        $etat = $sortie->getEtat();
        $site = $this->getUser()->getSite();
        $user = $this->getUser();
        $organisateur = $sortie->getParticipant();

        //modification possible si je suis l'organisateur de la sortie
        if ($user->getId() !== $organisateur->getId()) {
            $this->addFlash('danger', 'Impossible de modifier la sortie dont vous n\'êtes pas l\'organisateur!');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //modification possible uniquement si la sortie est a etat "créé"
        if ($etat->getLibelle() !== $etatCree->getLibelle()) {
            $this->addFlash('danger', 'Impossible de modifier la sortie déjà publiée!');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }
        //modification PAS possible si la date limite d'inscriptions supérieure au moment présent
        $dateLimiteInscriptions = $sortie->getDateLimiteInscription();
        if ($dateLimiteInscriptions <= new \DateTime('now')) {
            $this->addFlash(
                'danger',
                'Modification n\'est pas possible si la date limite d\'inscriptions a été dépassée.'
            );

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $lieu = $sortieForm->get('lieu')->getData();
            if ($lieu !== null) {
                $sortie->setLieu($lieu);
            }

            $sortie
                ->setParticipant($this->getUser())
                ->setSite($site);

            if ($sortieForm->get('creer')->isClicked()) {
                $etat = $etatRepository->find($etatCree->getId());
                $sortie->setEtat($etat);
            } elseif ($sortieForm->get('publier')->isClicked()) {
                $etat = $etatRepository->find($etatOuvert->getId());
                $sortie->setEtat($etat);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'La sortie a bien été modifiée!');

            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        return $this->render(
            'sortie/modifsortie.html.twig',
            [
                'sortieFormView' => $sortieForm->createView(),
                'lieuFormView' => $lieuForm->createView(),
                'site' => $site,
                'sortie' => $sortie,
            ]
        );
    }

    /**
     * @Route("/publier/{id}", name="publier", requirements={"id": "\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function publier($id, Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);


        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => EtatConstantes::OUVERT]);

        if ($sortie->getParticipant()->getId() !== $user->getId()) {
            $this->addFlash(
                "warning",
                "Vous n'êtes pas l'organisateur de cette sortie. Vous ne pouvez donc pas la publier."
            );
        } else {
            $sortie->setEtat($etatOuvert);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash("success", "Publication réussie.");
        }

        return $this->redirect($this->generateUrl('accueil'));
    }
}
