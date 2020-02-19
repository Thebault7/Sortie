<?php

// src/Service/ArchiverSortie.php
namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Constantes\EtatConstantes;
use Doctrine\ORM\EntityManagerInterface;

class ArchiverSortie
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function archiverSortie()
    {
        // On sélectionne toutes les sorties qui sont finies en base de données
        $etatRepository = $this->entityManager->getRepository(Etat::class);
        $etatFini = $etatRepository->findOneBy(['libelle' => EtatConstantes::CLOTURE]);
        $etatArchive = $etatRepository->findOneBy(['libelle' => EtatConstantes::ARCHIVE]);
        $sortieRepository = $this->entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        $dateDuJour = new \DateTime('now');

        // On passe en revue les dates de chaque sortie auxquelles on ajoute un mois.
        // Si cette date devient inférieure à la date du jour, c'est qu'il est temps
        // d'archiver cette sortie.
        for ($i = 0; $i < count($sorties); $i++) {
            $intervalDunMois = new \DateInterval("P1M"); // spécifie 1 mois à la période
            $dateSortiePlusUnMois = clone $sorties[$i]->getDateHeureDebut(); // on clone la sortie pour ne pas la modifier lors de l'ajout
            $dateSortiePlusUnMois->add($intervalDunMois);   // ajoute la période spécifiée

            if (date_format($dateSortiePlusUnMois, 'Y-m-d') <= date_format($dateDuJour, "Y-m-d")) {
                $sorties[$i]->setEtat($etatArchive);
                $this->entityManager->persist($sorties[$i]);
                $this->entityManager->flush();
            }
        }
    }
}