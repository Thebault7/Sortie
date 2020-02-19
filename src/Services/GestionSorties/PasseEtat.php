<?php


namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Constantes\EtatConstantes;
use Doctrine\ORM\EntityManagerInterface;

class PasseEtat
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    // etat 'passe' si date fin sortie depasse et que l'etat n'est pas 'archive' ou 'annule'
    public function setEtatPasse()
    {

        $etatRepository = $this->em->getRepository(Etat::class);
        $etatArchive = $etatRepository->findOneBy(['libelle' => EtatConstantes::ARCHIVE]);
        $etatAnnule = $etatRepository->findOneBy(['libelle' => EtatConstantes::ANNULE]);
        $etatPasse = $etatRepository->findOneBy(['libelle' => EtatConstantes::PASSE]);

        $sortieRepository = $this->em->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        for ($i = 0; $i < count($sorties); $i++) {
            $dateDebutSortie = $sorties[$i]->getDateHeureDebut();
            $dureeSortie = $sorties[$i]->getDuree();
            $dateFinSortie = $dateDebutSortie->modify('+' . $dureeSortie . ' minutes');
            $maintenant = new \DateTime();
            $etatId = $sorties[$i]->getEtat()->getId();

            if ($dateFinSortie <= $maintenant && $etatId !== $etatAnnule && $etatId !== $etatArchive) {
                $sorties[$i]->setEtat($etatPasse);
                $this->em->persist($sorties[$i]);
                $this->em->flush();
            }
        }
    }
}