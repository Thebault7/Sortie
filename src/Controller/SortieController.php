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
            $etat = $etatRepository->find(1);

           if($sortieForm->get('creer')->isClicked()){

            $site = $this->getUser()->getSite();

               $lieu = $sortieForm->get('lieuListe')->getData();
               if($lieu !== null){
                        $sortie->setLieu($lieu);
               }

               $sortie
                   ->setEtat($etat)
                   ->setParticipant($this->getUser())
                   ->setSite($site);

               $entityManager->persist($sortie);
               $entityManager->flush();
           }
           elseif ($sortieForm->get('publier')->isClicked()){

           }

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
            $sortie = $sortieRepository->find(1); //find($id);
            return $this->render('sortie/afficher.html.twig', compact('sortie'));
    }

    /**
     * @Route("/inscription/{id}", name="inscription", requirements={"id": "\d+"})
     */
    public function sinscrire($id, EntityManagerInterface $entityManager){
        $user = $this->getUser();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $sortie->addParticipant($user);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'Votre inscription a été prise en compte!');
        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/supprimer/{id}", name="supprimer", requirements={"id": "\d+"})
     */
    public function supprimer($id, EntityManagerInterface $entityManager){
        return $this->render('sortie/supprimer.html.twig', compact('sortie'));
    }

    /**
     * @Route("/modifsortie/{id}", name="modifsortie", requirements={"id": "\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifsortie($id, Request $request, EntityManagerInterface $entityManager)
    {
        $site = $this->getUser()->getSite();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $etatRepository = $entityManager->getRepository(Etat::class);
            $etat = $etatRepository->find(1);

            if($sortieForm->get('creer')->isClicked()){
                $site = $this->getUser()->getSite();

                $lieu = $sortieForm->get('lieuListe')->getData();
                if($lieu !== null){
                    $sortie->setLieu($lieu);
                }

                $sortie
                    ->setEtat($etat)
                    ->setParticipant($this->getUser())
                    ->setSite($site);


                $entityManager->persist($sortie);
                $entityManager->flush();
            }
            elseif ($sortieForm->get('publier')->isClicked()){

            }
            $this->addFlash('success', 'La sortie a bien été modifiée!');
            return $this->redirectToRoute("accueil");
        }

        return $this->render('sortie/modifsortie.html.twig', ['sortieFormView'=>$sortieForm->createView(), 'site'=>$site]);

    }
}
