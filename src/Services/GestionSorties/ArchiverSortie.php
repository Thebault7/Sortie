<?php

// src/Service/ArchiverSortie.php
namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;

class ArchiverSortie
{
    public function __construct()
    {
    }

    public function archiverSortie($entityManager)
    {
        // On sélectionne toutes les sorties qui sont finies en base de données
        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatFini = $etatRepository->findOneBy(['libelle' => 'Fini']);
        $etatArchive = $etatRepository->findOneBy(['libelle' => 'Archive']);
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByEtat($etatFini->getId());

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
                $entityManager->persist($sorties[$i]);
                $entityManager->flush();
            }
        }
    }
}