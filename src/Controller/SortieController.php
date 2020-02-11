<?php

namespace App\Controller;

use App\Entity\Etat;
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
       // $idea->getAuthor($this->getUser()->getUsername());

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $etatRepository = $entityManager->getRepository(Etat::class);
            $etat = $etatRepository->find(1);
            $sortie
                ->setEtat($etat);

               // ->setIsPublished(true)
          //      ->setDateCreated(new \DateTime('now'));

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Une nouvelle sortie a été ajoutée!');
            die();
          //  return $this->redirectToRoute("idea_detail", ['id' => $idea->getId()]);
        }

        return $this->render('sortie/add.html.twig', ['sortieFormView'=>$sortieForm->createView()]);

    }
}
