<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ModifProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


class ModifProfilController extends Controller
{
    /**
     * @Route("/modifprofil", name="modifprofil")
     */
    public function modifprofil(EntityManagerInterface $entityManager)
    {
        $modifProfil = new Participant();
        $form = $this->createForm(ModifProfilType::class, $modifProfil);
        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        return $this->render('profil/modifprofil.html.twig', [
            'ModifProfilForm' => $form->createView(),
        ]);

    }

}