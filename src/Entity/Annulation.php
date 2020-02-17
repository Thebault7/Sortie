<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnnulationRepository")
 */
class Annulation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=400, nullable=false)
     * @Assert\NotBlank
     */
    private $motif;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Sortie", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $sortie;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->relation;
    }

    public function setSortie(Sortie $sortie): self
    {
        $this->sortie = $sortie;

        return $this;
    }
}
