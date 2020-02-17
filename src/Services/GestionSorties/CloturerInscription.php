<?php

// src/Service/CloturerInscription.php
namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;

class CloturerInscription
{
    public function __construct()
    {
    }

    public function cloturerInscriptionNbMax($entityManager)
    {
        // si le nombre d'inscripts atteint le nombre max, on clôture la sortie
        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouvert']);
        $etatFerme = $etatRepository->findOneBy(['libelle' => 'Fermé']);
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByEtat($etatOuvert->getId());

        for ($i = 0; $i < count($sorties); $i++) {
            $nbParticipantsMax = $sorties[$i]->getNbInscriptionMax();
            $nbInscrits = $sorties[$i]->getParticipants();
            if (count($nbInscrits) >= $nbParticipantsMax) {
                $sorties[$i]->setEtat($etatFerme);
                $entityManager->persist($sorties[$i]);
                $entityManager->flush();
            }
        }
    }

    public function cloturerInscriptionDate($entityManager)
    {
        // si la date limite d'inscription est atteinte, on clôture la sortie
    }
}