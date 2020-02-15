<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/lieu", name="lieu_")
 */
class LieuController extends Controller
{
    /**
     * @Route("/ajouter", name="ajout")
     */
    public function ajouter(Request $request, EntityManagerInterface $entityManager)
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()) {

            $villeRepository = $entityManager->getRepository(Ville::class);
            $ville = $villeRepository->findAll();

            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Un nouveau lieu a été ajoutée à la liste!');
            return $this->render('sortie/add.html.twig');
        }

        return $this->render('lieu/ajouter.html.twig', ['lieuFormView'=>$lieuForm->createView()]);
    }


}
