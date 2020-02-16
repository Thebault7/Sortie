<?php

// src/Service/DateDebutFiltre.php
namespace App\Services;

class DateDebutFiltre
{
    public function __construct($sorties, $dateDebut)
    {
        $this->sorties = $sorties;
        $this->dateDebut = $dateDebut;
    }

    public function dateDebutFiltre()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                if ($this->dateDebut <= date_format($this->sorties[$i]->getDateHeureDebut(), "Y-m-d")) {
                    $tableauFinal[$i] = $this->sorties[$i];
                } else {
                    $tableauFinal[$i] = false;
                }
            }
        }
        return $tableauFinal;
    }
}