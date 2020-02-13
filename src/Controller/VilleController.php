<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Entity\Lieu;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SiteController
 * @package App\Controller
 * @Route("/admin/ville", name="ville_")
 */
class VilleController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        // génération du formulaire
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        // on va chercher en base de données des villes
        $villeRepository = $entityManager->getRepository(Ville::class);
        $villes = $villeRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // mise en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('ville_index'));
        }

        return $this->render(
            'ville/index.html.twig',
            [
                'villeForm' => $form->createView(),
                'villes' => $villes,
            ]
        );
    }

    /**
     * @Route("/delete/{nom}", name="delete")
     * @param $nom
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function supprimer($nom, EntityManagerInterface $entityManager)
    {
        $noms = ['nom' => $nom];
        $villeRepository = $entityManager->getRepository(Ville::class);
        $villes = $villeRepository->findBy($noms);

        // Avant de supprimer une ville, il faut s'assurer qu'elle n'est pas déjà liée à un lieu.
        // Si la ville est utilisée, sa suppression n'est pas autorisée.
        // Mais pour récupérer les lieux associés, il faut convertir le nom
        // de la ville en son id dans la base de données.
        for ($i = 0; $i < count($villes); $i++) {
            $id[$i] = $villes[$i]->getId();
        }

        $lieuRepository = $entityManager->getRepository(Lieu::class);
        for ($i = 0; $i < count($id); $i++) {
            $lieux[$i] = $lieuRepository->findByVille($id[$i]);
        }

        if ($lieux[0] === []) {
            for ($i = 0; $i < count($villes); $i++) {
                $entityManager->remove($villes[$i]);
                $entityManager->flush();
                $this->addFlash("succes", "Suppresion de la ville réussie.");

                return $this->redirectToRoute('ville_index');
            }
        } else {
            $this->addFlash(
                "echec",
                "Un ou plusieurs lieux utilisent cette ville. On ne peut pas supprimer cette ville."
            );

            return $this->redirectToRoute('ville_index');
        }

        return $this->redirect($this->generateUrl('ville_index'));
    }

    /**
     * @Route ("/modify", name="modify")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return
     */
    public function modify(Request $request, EntityManagerInterface $entityManager)
    {
        $villeModify = $request->request->get('ville_modify');
        $villeHidden = $request->request->get('ville_hidden');
        $codePostalModify = $request->request->get('cp_modify');
        $codePostalHidden = $request->request->get('cp_hidden');

        if ($villeModify !== $villeHidden || $codePostalModify !== $codePostalHidden) {
            // on va chercher le site en base de données grâce à son nom $siteHidden,
            // puis on le modifie en lui donnant le nouveau nom $siteModify
            $villeRepository = $entityManager->getRepository(Ville::class);
            $villes = $villeRepository->findByName($villeHidden);

            // on remplace le nom
            for ($i = 0; $i < count($villes); $i++) {
                $villes[$i]->setNom($villeModify);
                $villes[$i]->setCodePostal($codePostalModify);
            }
            // mise en base de données
            for ($i = 0; $i < count($villes); $i++) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($villes[$i]);
                $entityManager->flush();
            }
        } else {
            if ($villeModify === $villeHidden) {
                $this->addFlash(
                    "echec",
                    "Le nom de la ville après modification est le même qu'avant. Aucune modification n'a été faite."
                );
            }
            if ($codePostalModify === $codePostalHidden) {
                $this->addFlash(
                    "echec",
                    "Le code postal après modification est le même qu'avant. Aucune modification n'a été faite."
                );
            }
        }

        return $this->redirect($this->generateUrl('ville_index'));
    }

    /**
     * @Route("/filter", name="filter")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     */
    public function filter(Request $request, EntityManagerInterface $entityManager)
    {
        // génération du formulaire
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        $nomAChercher = $request->request->get('ville_filter');
        $villeRepository = $entityManager->getRepository(Ville::class);
        $villes = $villeRepository->findByName($nomAChercher);

        return $this->render(
            'ville/index.html.twig',
            [
                'villeForm' => $form->createView(),
                'villes' => $villes,
            ]
        );
    }
}
