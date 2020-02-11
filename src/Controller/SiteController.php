<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Site;
use App\Form\SiteType;


class SiteController extends Controller
{
    /**
     * @Route("/site", name="site")
     * @param Request $request
     * @return
     */
    public function index(Request $request)
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        return $this->render('site/index.html.twig', [
            'SiteForm' => $form->createView(),
        ]);
    }
}
