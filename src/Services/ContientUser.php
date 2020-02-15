<?php

// src/Service/ContientUser.php
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participants;

class ContientUser
{
    public function __construct($sortie, $user)
    {
        $this->sortie = $sortie;
        $this->user = $user;
    }

    public function contientUser(EntityManagerInterface $entityManager)
    {
        $contientUser = false;

        $participantsRepository = $entityManager->getRepository(Participants::class);
        $listParticipants = $participantsRepository->findAll();

        for ($j = 0; $j < count($listParticipants); $j++) {
            if ($listParticipants[$j]->getSortie()->getId() === $this->sortie->getId()
                && $listParticipants[$j]->getParticipant()->getId() === $this->user->getId()) {
                $contientUser = true;
            }
        }

        return $contientUser;
    }
}
