<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $rep = $request->query->all();

        $nom = $rep['lieu']['nom'];
        $rue = $rep['lieu']['rue'];
        $villeListe = $rep['lieu']['villeListe'];
        $nomVille = $rep['lieu']['ville']['nom'];
        $codePostal = $rep['lieu']['ville']['codePostal'];

        $lieu = new Lieu();
        $ville = new Ville();

        if(!($villeListe)){

            $villeRepository = $entityManager->getRepository(Ville::class);
            $villeARetrouver = $villeRepository->findByCodePostalEtNom($codePostal, $nomVille);

            if($villeARetrouver){
                $villeRetrouvee = $villeRepository->find($villeARetrouver[0]->getId());
                $lieu->setVille($villeRetrouvee);
            }
            else{
                $ville
                    ->setNom($nomVille)
                    ->setCodePostal($codePostal);
                $lieu->setVille($ville);
            }

        }
        else{
            $ville = $entityManager->getRepository(Ville::class)->find($villeListe);
            $lieu->setVille($ville);
        }

        $lieu
            ->setNom($nom)
            ->setRue($rue);

        $entityManager->persist($lieu);
        $entityManager->flush();

        return new JsonResponse([
            "id" => $lieu->getId(),
            "nom" => $lieu->getNom(),
        ]);




    /*    $lieuRepo = $this->getDoctrine()->getRepository(Lieu::class);
        $lieu = $lieuRepo->find(1);

        return new JsonResponse([
            "id" => $lieu->getId(),
            "name" => $lieu->getName(),
        ]);*/
      /*  die();
        $request = $this->get('request');
        $nom = $request->get('nom');
       // $form->bindRequest($request);
        // data is an array with "name", "email", and "message" keys
      //  $data = $form->getData();
dump($nom);
        die();*/
       /* $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()) {

            $villeRepository = $entityManager->getRepository(Ville::class);
            $ville = $villeRepository->findAll();

            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Un nouveau lieu a été ajoutée à la liste!');
            return $this->render('sortie/add.html.twig');
        }*/

       /* return $this->render('lieu/ajouter.html.twig', ['lieuFormView'=>$lieuForm->createView()]);*/
    }


}
