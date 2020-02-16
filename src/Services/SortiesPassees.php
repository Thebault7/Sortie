<?php

// src/Service/OsortiesPassees.php
namespace App\Services;

class SortiesPassees
{
    public function __construct($sorties)
    {
        $this->sorties = $sorties;
    }

    public function sortiesPassees()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                if ($this->sorties[$i]->getEtat()->getLibelle() === 'Fini') {
                    $tableauFinal[$i] = $this->sorties[$i];
                } else {
                    $tableauFinal[$i] = false;
                }
            }
        }
        return $tableauFinal;
    }
}