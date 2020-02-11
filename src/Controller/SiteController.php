<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Site;
use App\Form\SiteType;


class SiteController extends Controller
{
    /**
     * @Route("/admin/site", name="site")
     * @param Request $request
     * @return
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

        return $this->render('site/index.html.twig', [
            'SiteForm' => $form->createView(),
            'Sites' => $sites
        ]);
    }
}
