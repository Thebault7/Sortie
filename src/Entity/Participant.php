<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantRepository")
 * @UniqueEntity(fields={"mail"}, message="Un compte avec le même identifiant existe déjà.")
 * @UniqueEntity(fields={"pseudo"}, message="Un compte avec le même pseudo existe déjà.")
 */
//, "pseudo"
class Participant implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=30,
     *     minMessage="Le champ 'nom' accepte au minimim {{ limit }} caractères",
     *     maxMessage="Le champ 'nom' accepte au maximum {{ limit }} caractères"
     * )
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=30,
     *     minMessage="Le champ 'prénom' accepte au minimim {{ limit }} caractères",
     *     maxMessage="Le champ 'prénom' accepte au maximum {{ limit }} caractères"
     * )
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=15)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=4,
     *     max=15,
     *     minMessage="Le champ 'téléphone' accepte au minimim {{ limit }} caractères",
     *     maxMessage="Le champ 'téléphone' accepte au maximum {{ limit }} caractères"
     * )
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\Email
     */
    private $mail;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $actif;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, unique=true)
     * @Assert\Length(
     *     min=4,
     *     max=50,
     *     minMessage="Le champ 'pseudo' accepte au minimim {{ limit }} caractères",
     *     maxMessage="Le champ 'pseudo' accepte au maximum {{ limit }} caractères"
     * )
     */
    private $pseudo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="participant")
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="participant")
     */
    private $organisateur;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sortie", inversedBy="participants")
     */
    private $inscrit;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\Length(
     *     min=8,
     *     max=30,
     *     minMessage="Le mot de passe accepte au minimim {{ limit }} caractères.",
     *     maxMessage="Le champ mot de passe accepte au maximum {{ limit }} caractères."
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *     min=6,
     *     max=255,
     *     minMessage="Le nom du fichier image accepte au minimim {{ limit }} caractères",
     *     maxMessage="Le nom du fichier image accepte au maximum {{ limit }} caractères"
     * )
     */
    private $photo;


    public function __construct()
    {
        $this->organisateur = new ArrayCollection();
        $this->inscrit = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

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

    /**
     * @return Collection|Sortie[]
     */
    public function getOrganisateur(): Collection
    {
        return $this->organisateur;
    }

    public function addOrganisateur(Sortie $organisateur): self
    {
        if (!$this->organisateur->contains($organisateur)) {
            $this->organisateur[] = $organisateur;
            $organisateur->setParticipant($this);
        }

        return $this;
    }

    public function removeOrganisateur(Sortie $organisateur): self
    {
        if ($this->organisateur->contains($organisateur)) {
            $this->organisateur->removeElement($organisateur);
            // set the owning side to null (unless already changed)
            if ($organisateur->getParticipant() === $this) {
                $organisateur->setParticipant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getInscrit(): Collection
    {
        return $this->inscrit;
    }

    public function addInscrit(Sortie $inscrit): self
    {
        if (!$this->inscrit->contains($inscrit)) {
            $this->inscrit[] = $inscrit;
        }

        return $this;
    }

    public function removeInscrit(Sortie $inscrit): self
    {
        if ($this->inscrit->contains($inscrit)) {
            $this->inscrit->removeElement($inscrit);
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        if (empty($this->roles)) {
            $this->roles = ['ROLE_USER'];
        }
        if ($this->administrateur === true) {
            $this->roles = ['ROLE_ADMIN'];
        }
        if ($this->actif === false) {
            $this->roles = ['ROLE_INACTIF'];
        }

        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->mail;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }
}
