<?php

// src/Service/CloturerInscription.php
namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;
use Doctrine\ORM\EntityManagerInterface;

class CloturerInscription
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
            $this->em = $entityManager;
    }

    // si le nombre d'inscripts atteint le nombre max, on clôture la sortie
    public function cloturerInscriptionNbMax()
    {
        $etatRepository = $this->em->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouvert']);
        $etatFerme = $etatRepository->findOneBy(['libelle' => 'Clôturé']);

        $sortieRepository = $this->em->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByEtat($etatOuvert->getId());

        for ($i = 0; $i < count($sorties); $i++) {
            $nbParticipantsMax = $sorties[$i]->getNbInscriptionMax();
            $nbInscrits = $sorties[$i]->getParticipants();
            if (count($nbInscrits) >= $nbParticipantsMax) {
                $sorties[$i]->setEtat($etatFerme);
                $this->em->persist($sorties[$i]);
                $this->em->flush();
            }
        }
    }
    // si la date limite d'inscription est atteinte, on clôture la sortie
    public function cloturerDateLimite()
    {
        $etatRepository = $this->em->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouvert']);
        $etatFerme = $etatRepository->findOneBy(['libelle' => 'Clôturé']);

        $sortieRepository = $this->em->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        for ($i = 0; $i < count($sorties); $i++) {
            $dateLimiteInsciption = $sorties[$i]->getDateLimiteInscription();
            $dateDebutSortie = $sorties[$i]->getDateHeureDebut();
                    if($dateLimiteInsciption <= new \DateTime() && $dateDebutSortie > new \DateTime()){
                        $sorties[$i]->setEtat($etatFerme);
                        $this->em->persist($sorties[$i]);
                        $this->em->flush();
                    }
        }

    }
}