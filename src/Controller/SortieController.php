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
            die();
            return $this->redirectToRoute("accueil");
        }
        return $this->render('sortie/add.html.twig', ['sortieFormView'=>$sortieForm->createView(), 'site'=>$site]);
    }

    /**
     * @Route("/afficher/{id}", name="afficher", requirements={"id": "\d+"})
     */
    public function afficherSortie($id, EntityManagerInterface $entityManager){
            $sortieRepository = $entityManager->getRepository(Sortie::class);
            $sortie = $sortieRepository->find(13); //find($id);

            return $this->render('sortie/afficher.html.twig', compact('sortie'));
    }

    /**
     * @Route("/modifier/{id}", name="modifier", requirements={"id": "\d+"})
     */
    public function modifier($id, EntityManagerInterface $entityManager){
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);
        return $this->render('sortie/modifier.html.twig', compact('sortie'));
    }

    /**
     * @Route("/modifsortie", name="modifsortie")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifsortie(Request $request, EntityManagerInterface $entityManager)
    {
        $sortie = new Sortie();
        // $idea->getAuthor($this->getUser()->getUsername());
        $site = $this->getUser()->getSite();
        //  $participantRepository = $entityManager->getRepository(Participant::class);

        //->getSite();


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

            //      ->setDateCreated(new \DateTime('now'));



            $this->addFlash('success', 'Une nouvelle sortie a été ajoutée!');
            die();
            return $this->redirectToRoute("accueil");
        }

        return $this->render('sortie/modifsortie.html.twig', ['sortieFormView'=>$sortieForm->createView(), 'site'=>$site]);

    }
}
