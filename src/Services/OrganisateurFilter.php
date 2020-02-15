<?php

// src/Service/OrganisateurFilter.php
namespace App\Services;

class OrganisateurFilter
{
    public function __construct($sorties, $user)
    {
        $this->sorties = $sorties;
        $this->user = $user;
    }

    function organisateur()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i]->getParticipant()->getId() === $this->user->getId()) {
                $tableauFinal[$i] = $this->sorties[$i];
            } else {
                $tableauFinal[$i] = false;
            }
        }
        return $tableauFinal;
    }
}