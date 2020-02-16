<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SortieRepository")
 */
class Sortie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="Le champ 'description et infos' accepte au minimim 2 caractères",
     *     maxMessage="Le champ 'description et infos' accepte au maximum 50 caractères"
     * )
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Assert\Type(
     *      type = "\DateTime",
     *      message = "Date et heure de la sortie doit être conforme au format date",
     * )
     * @Assert\GreaterThan("+3 minutes", message="La date et heure de début de la sortie doit être supérieure à {{ value }}")
     * @Assert\Expression(
     *     "this.getDateLimiteInscription() < this.getDateHeureDebut()",
     *     message="La date limite d'incriptions doit être inférieure à la date de début de la sortie"
     * )
     *
     */

    private $dateHeureDebut; //

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(
     *   type="integer",
     *   message="La valeur du champ 'nombre de places' doit être un nombre entier."
     * )
     * @Assert\Range(
     *      min = 5,
     *      max = 1440,
     *      minMessage = "La valeur minimum du champ durée est de {{ limit }} minutes",
     *      maxMessage = "La valeur max du champ durée est de {{ limit }} minutes"
     * )
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Assert\Type(
     *      type = "\DateTime",
     *      message = "Date et heure de la limite d'inscriptions doit être conforme au format date",
     * )

     */
    private $dateLimiteInscription; //* @Assert\GreaterThan("+2 minutes", message="La date et heure de début de la sortie doit être supérieure à {{ value }}")

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Type(
     *   type="integer",
     *   message="La valeur du champ 'nombre de places' doit être un nombre entier."
     * )
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Il y a au moins une personne qui doit pouvoir participer à cette sortie",
     * )
     */
    private $nbInscriptionMax;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *     max=255,
     *     maxMessage="Le champ 'description et infos' accepte au maximum 255 caractères"
     * )
     */
    private $infosSortie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etat", inversedBy="sortie", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lieu", inversedBy="sortie", cascade={"persist"}, fetch="EAGER")
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="sortie", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Participant", inversedBy="organisateur", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $participant;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): self
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;

        return $this;
    }
}
