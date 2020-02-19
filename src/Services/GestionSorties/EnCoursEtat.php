<?php


namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Constantes\EtatConstantes;
use Doctrine\ORM\EntityManagerInterface;

class EnCoursEtat
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    // set etat a "activite en cours" si date debut depassee et la duree de la sortie n'a pas ete depassee
    public function setEtatEnCours()
    {
        $etatRepository = $this->em->getRepository(Etat::class);
        $etatEnCours = $etatRepository->findOneBy(['libelle' => EtatConstantes::ACTIVITE_EN_COURS]);

        $sortieRepository = $this->em->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        for ($i = 0; $i < count($sorties); $i++) {
            $dateDebutSortie = $sorties[$i]->getDateHeureDebut();
            $dureeSortie = $sorties[$i]->getDuree();
            $maintenant = new \DateTime();
            if ($dateDebutSortie <= $maintenant && $dateDebutSortie->modify('+' . $dureeSortie . ' minutes') > $maintenant) {
                $sorties[$i]->setEtat($etatEnCours);
                $this->em->persist($sorties[$i]);
                $this->em->flush();
            }
        }
    }
}