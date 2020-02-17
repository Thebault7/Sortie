<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
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
        $sortie = new Sortie();
        $site = $this->getUser()->getSite();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $etatRepository = $entityManager->getRepository(Etat::class);

            $lieu = $sortieForm->get('lieuListe')->getData();
            if($lieu !== null){
                $sortie->setLieu($lieu);
            }

            $sortie
                ->setParticipant($this->getUser())
                ->setSite($site);

           if($sortieForm->get('creer')->isClicked()){
               $etat = $etatRepository->find(1);
               $sortie->setEtat($etat);
           }
           elseif ($sortieForm->get('publier')->isClicked()){
               $etat = $etatRepository->find(2);
               $sortie->setEtat($etat);
           }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Une nouvelle sortie a été ajoutée!');

            return $this->redirectToRoute("accueil");
        }
        return $this->render('sortie/add.html.twig', ['sortieFormView'=>$sortieForm->createView(), 'site'=>$site]);
    }

    /**
     * @Route("/afficher/{id}", name="afficher", requirements={"id": "\d+"})
     */
    public function afficherSortie($id, EntityManagerInterface $entityManager){
            $sortieRepository = $entityManager->getRepository(Sortie::class);
            $sortie = $sortieRepository->find($id);
            return $this->render('sortie/afficher.html.twig', compact('sortie'));
    }

    /**
     * @Route("/inscription/{id}", name="inscription", requirements={"id": "\d+"})
     */
    public function sinscrire($id, EntityManagerInterface $entityManager){
        $user = $this->getUser();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $participants = $sortie->getParticipants();

        // inscription impossible pour l'organisateur de la sortie
          if($user->getId() === $sortie->getParticipant()->getId()){
            $this->addFlash('warning', 'Echec inscription. Vous êtes l\'organisateur de cette sortie...');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        // si le participant essaie de s'incrire une 2eme fois a la sortie
        foreach ($participants as $participant){
            if($participant->getId() === $user->getId()){
                $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie!');
                return $this->redirectToRoute('sortie_afficher', compact('id'));
            }
        }

        //si le nombre d'inscriptions max a été atteint
        $nbInscripMax = $sortie->getNbInscriptionMax();
        if(count($participants) >= $nbInscripMax ){
            $this->addFlash('danger', 'Le nombre maximal d\'inscriptions a dèjà été atteint!');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //si etat nest pas "ouvert"
        $etat = $sortie->getEtat();
        if($etat->getLibelle() !== "Ouvert"){
            $this->addFlash('danger', 'Il n\'est pas possible de s\'inscrire à cette sortie. Il est uniquement possible de s\'inscrire aux sorties dont l\'état est égal à "ouvert". ');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //si la date limite d'inscriptions est dépassée
        $dateLimiteInscriptions = $sortie->getDateLimiteInscription();
        if($dateLimiteInscriptions <= new \DateTime('now')){
            $this->addFlash('danger', 'La date limite d\'inscriptions a été dépassée.');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
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
    public function desister($id, EntityManagerInterface $entityManager){

        $user = $this->getUser();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $participants = $sortie->getParticipants();
        $etat = $sortie->getEtat();
        $dateDebutSortie = $sortie->getDateHeureDebut();

        //desistement impossible si je suis l'organisateur de la sortie
        if($user->getId() === $sortie->getParticipant()->getId()) {
            $this->addFlash('warning', 'Echec désistement. Vous êtes l\'organisateur de cette sortie...');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //verifier si l'etat est egal a "ouvert" --> sinon desistement n'est pas possible
        if($etat->getLibelle() !== "Ouvert"){
            $this->addFlash('danger', 'L\'option \'désistement\' n\'est plus valide! ');
        }
        //si user n'est pas sur la liste des participants
        elseif(!($participants->contains($user))){
            $this->addFlash('danger', 'Vous ne pouvez pas désister car vous ne participez pas à cette sortie!');
        }
        // verif si la sortie n'a pas déja débuté
        elseif($dateDebutSortie <= new \DateTime('now')){
            $this->addFlash('danger', 'Sortie est dèjà en cours! Impossible de désister!');
        }
        // verifier encore si le user est bien inscrit a cette sortie avant de le supprimer de la liste
        elseif($participants->contains($user)){
            // suppression de participant
            $sortie->removeParticipant($user);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Votre désistement a été pris en compte!');
        }

        return $this->redirectToRoute('sortie_afficher', compact('id'));
    }


    /**
     * @Route("/supprimer/{id}", name="supprimer", requirements={"id": "\d+"})
     */
    public function supprimer($id, EntityManagerInterface $entityManager){

        return $this->render('sortie/supprimer.html.twig', compact('sortie'));
    }

    /**
     * @Route("/messorties", name="messorties")
     */
    public function lister(EntityManagerInterface $entityManager){

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
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);
        $etatRepository = $entityManager->getRepository(Etat::class);
        $etat = $sortie->getEtat();
        $site = $this->getUser()->getSite();
        $user = $this->getUser();
        $organisateur = $sortie->getParticipant();

        //modification possible si je suis l'organisateur de la sortie
        if($user->getId() !== $organisateur->getId()){
            $this->addFlash('danger', 'Impossible de modifier la sortie dont vous n\'êtes pas l\'organisateur!');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        //modification possible uniquement si la sortie est a etat "créé"
       if($etat->getLibelle() !== "Créé"){
            $this->addFlash('danger', 'Impossible de modifier la sortie déjà publiée!');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }
        //modification PAS possible si la date limite d'inscriptions supérieure au moment présent
        $dateLimiteInscriptions = $sortie->getDateLimiteInscription();
        if($dateLimiteInscriptions <= new \DateTime('now')){
            $this->addFlash('danger', 'Modification n\'est pas possible si la date limite d\'inscriptions a été dépassée.');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $lieu = $sortieForm->get('lieuListe')->getData();
            if ($lieu !== null) {
                $sortie->setLieu($lieu);
            }

            $sortie
                ->setParticipant($this->getUser())
                ->setSite($site);

            if($sortieForm->get('creer')->isClicked()){
                $etat = $etatRepository->find(1);
                $sortie->setEtat($etat);
            }
            elseif ($sortieForm->get('publier')->isClicked()){
                $etat = $etatRepository->find(2);
                $sortie->setEtat($etat);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'La sortie a bien été modifiée!');
            return $this->redirectToRoute('sortie_afficher', compact('id'));
        }

        return $this->render('sortie/modifsortie.html.twig', ['sortieFormView'=>$sortieForm->createView(), 'site'=>$site, 'sortie'=>$sortie]);
    }
}
