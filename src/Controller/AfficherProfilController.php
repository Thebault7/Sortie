<?php


namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AfficherProfilController extends Controller
{
    /**
     * @Route("/afficherprofil", name="afficherprofil")
     */
    public function afficherprofil(EntityManagerInterface $entityManager)
    {
        $participant = $this->getUser();

        return $this->render
        (
            'profil/afficherprofil.html.twig',
            compact( 'participant'));

    }

}