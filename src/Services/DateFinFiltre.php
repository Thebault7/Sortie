<?php

// src/Service/DateFinFiltre.php
namespace App\Services;

class DateFinFiltre
{
    public function __construct($sorties, $dateFin)
    {
        $this->sorties = $sorties;
        $this->dateFin = $dateFin;
    }

    public function dateFinFiltre()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                if ($this->dateFin >= date_format($this->sorties[$i]->getDateHeureDebut(), "Y-m-d")) {
                    $tableauFinal[$i] = $this->sorties[$i];
                } else {
                    $tableauFinal[$i] = false;
                }
            }
        }
        return $tableauFinal;
    }
}