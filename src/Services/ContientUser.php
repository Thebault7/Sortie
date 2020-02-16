<?php

// src/Service/ContientUser.php
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sortie;

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

        $listParticipants = $this->sortie->getParticipants();

        for ($j = 0; $j < count($listParticipants); $j++) {
            if ($listParticipants[$j]->getId() === $this->user->getId()) {
                $contientUser = true;
            }
        }

        return $contientUser;
    }
}
