<?php

// src/Service/SortiesInscrit.php
namespace App\Services;

use App\Services\ContientUser;

class SortiesInscrit
{
    public function __construct($sorties, $user)
    {
        $this->sorties = $sorties;
        $this->user = $user;
    }

    public function sortiesInscrit()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                $contientUserClass = new ContientUser($this->sorties[$i], $this->user);
                $contientUser = $contientUserClass->contientUser();
                if ($contientUser) {
                    $tableauFinal[$i] = $this->sorties[$i];
                } else {
                    $tableauFinal[$i] = false;
                }
            }
        }
        return $tableauFinal;
    }

    public function sortiesNonInscrit()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                $contientUserClass = new ContientUser($this->sorties[$i], $this->user);
                $contientUser = $contientUserClass->contientUser();
                if ($contientUser) {
                    $tableauFinal[$i] = false;
                } else {
                    $tableauFinal[$i] = $this->sorties[$i];
                }
            }
        }
        return $tableauFinal;
    }
}