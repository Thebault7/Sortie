<?php

// src/Service/SiteFiltre.php
namespace App\Services;

class SiteFiltre
{
    public function __construct($sorties, $siteId)
    {
        $this->sorties = $sorties;
        $this->siteId = $siteId;
    }

    public function siteFiltre()
    {
        $tableauFinal = [];
        for ($i = 0; $i < count($this->sorties); $i++) {
            if ($this->sorties[$i] === false) {
                $tableauFinal[$i] = false;
            } else {
                if ($this->sorties[$i]->getSite()->getId() == $this->siteId) {
                    $tableauFinal[$i] = $this->sorties[$i];
                } else {
                    $tableauFinal[$i] = false;
                }
            }
        }
        return $tableauFinal;
    }
}