<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UploadRepository::class)
 */
class Upload
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero;

    /**
     * @ORM\ManyToOne(targetEntity=Courrier::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $courier;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valide;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDisabled;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $n_bl;

    /**
     * @ORM\Column(type="boolean")
     */
    private $second_valide;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valide_recipient;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
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

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }


    public function getCourier(): ?Courrier
    {
        return $this->courier;
    }

    public function setCourier(?Courrier $courier): self
    {
        $this->courier = $courier;

        return $this;
    }

    public function getValide(): ?bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): self
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsDisabled(): ?bool
    {
        return $this->isDisabled;
    }

    /**
     * @param mixed $isDisabled
     */
    public function setIsDisabled(bool $isDisabled): self
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    public function getNBl(): ?string
    {
        return $this->n_bl;
    }

    public function setNBl(string $n_bl): self
    {
        $this->n_bl = $n_bl;

        return $this;
    }

    public function getSecondValide(): ?bool
    {
        return $this->second_valide;
    }

    public function setSecondValide(bool $second_valide): self
    {
        $this->second_valide = $second_valide;

        return $this;
    }

    public function getValideRecipient(): ?bool
    {
        return $this->valide_recipient;
    }

    public function setValideRecipient(bool $valide_recipient): self
    {
        $this->valide_recipient = $valide_recipient;

        return $this;
    }
}
