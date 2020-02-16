<?php

// src/Service/NomSortieFiltre.php
namespace App\Services;

class NomSortieFiltre
{
    public function __construct($sorties, $nom)
    {
        $this->sorties = $sorties;
        $this->nom = $nom;
    }

    public function nomSortieFiltre()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                // utilise une regex pour déterminer si $nom est contenu dans le nom d'une sortie
                if (preg_match('/\w*' . $this->nom . '\w*/', $this->sorties[$i]->getNom())) {
                    $tableauFinal[$i] = $this->sorties[$i];
                } else {
                    $tableauFinal[$i] = false;
                }
            }
        }
        return $tableauFinal;
    }
}