<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Site;
use App\Form\SiteType;


/**
 * Class SiteController
 * @package App\Controller
 * @Route("/admin/site", name="site_")
 */
class SiteController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        // génération du formulaire
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        // on va chercher en base de données les sites ENI
        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // mise en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('site_index'));
        }

        return $this->render(
            'site/index.html.twig',
            [
                'SiteForm' => $form->createView(),
                'Sites' => $sites,
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
        $siteRepository = $entityManager->getRepository(Site::class);
        $sitesENI = $siteRepository->findBy($noms);

        // Avant de supprimer un site, il faut s'assurer qu'il n'est pas déjà lié à un utilisateur
        // ou à une sortie. Si le site est utilisé, sa suppression n'est pas autorisée.
        // Mais pour récupérer les sorties et les utilisateurs associés, il faut convertir le nom
        // du site en son id dans la base de données.
        for ($i = 0; $i < count($sitesENI); $i++) {
            $id[$i] = $sitesENI[$i]->getId();
        }

        $participantRepository = $entityManager->getRepository(Participant::class);
        for ($i = 0; $i < count($id); $i++) {
            $participants[$i] = $participantRepository->findBySite($id[$i]);
        }

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        for ($i = 0; $i < count($id); $i++) {
            $sorties[$i] = $sortieRepository->findBySite($id[$i]);
        }

        if ($participants[0] === []) {
            if ($sorties[0] === []) {
                for ($i = 0; $i < count($sitesENI); $i++) {
                    $entityManager->remove($sitesENI[$i]);
                    $entityManager->flush();
                    $this->addFlash("succes", "Suppresion du site réussie.");

                    return $this->redirectToRoute('site_index');
                }
            } else {
                $this->addFlash(
                    "echec",
                    "Une ou plusieurs sorties sont situées sur ce site. On ne peut pas supprimer ce site."
                );

                return $this->redirectToRoute('site_index');
            }
        } else {
            $this->addFlash(
                "echec",
                "Un ou plusieurs utilisateurs sont inscrits sur ce site. On ne peut pas supprimer ce site."
            );

            return $this->redirectToRoute('site_index');
        }

        return $this->redirect($this->generateUrl('site_index'));
    }

    /**
     * @Route ("/modify", name="modify")
     */
    public function modify(Request $request, EntityManagerInterface $entityManager)
    {
        $siteModify = $request->request->get('site_modify');
        $siteHidden = $request->request->get('site_hidden');

        // on va chercher le site en base de données grâce à son nom $siteHidden,
        // puis on le modifie en lui donnant le nouveau nom $siteModify
        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findByName($siteHidden);

        // on remplace le nom
        for ($i = 0; $i < count($sites); $i++) {
            $sites[$i]->setNom($siteModify);
        }
        // mise en base de données
        for ($i = 0; $i < count($sites); $i++) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sites[$i]);
            $entityManager->flush();
        }

        return $this->redirect($this->generateUrl('site_index'));
    }

    /**
     * @Route("/filter", name="filter")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     */
    public function filter(Request $request, EntityManagerInterface $entityManager)
    {
        // génération du formulaire
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        $nomAChercher = $request->request->get('site_filter');
        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findByName($nomAChercher);

        return $this->render(
            'site/index.html.twig',
            [
                'SiteForm' => $form->createView(),
                'Sites' => $sites,
            ]
        );
    }
}
