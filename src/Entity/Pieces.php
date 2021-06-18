<?php

namespace App\Entity;

use App\Repository\PiecesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PiecesRepository::class)
 */
class Pieces
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $n_cmd;

    /**
     * @ORM\Column(type="bigint")
     */
    private $n_recept;

    /**
     * @ORM\Column(type="bigint")
     */
    private $n_bl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fournisseur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rayon;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $d_reception;

    /**
     * @ORM\Column(type="bigint")
     */
    private $montant_HT;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $valide;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_disabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $second_valide;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valide_recipient;

    /**
     * @ORM\ManyToOne(targetEntity=Courrier::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $courrier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNCmd(): ?string
    {
        return $this->n_cmd;
    }

    public function setNCmd(string $n_cmd): self
    {
        $this->n_cmd = $n_cmd;

        return $this;
    }

    public function getNRecept(): ?string
    {
        return $this->n_recept;
    }

    public function setNRecept(string $n_recept): self
    {
        $this->n_recept = $n_recept;

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

    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    public function setFournisseur(string $fournisseur): self
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getRayon(): ?string
    {
        return $this->rayon;
    }

    public function setRayon(string $rayon): self
    {
        $this->rayon = $rayon;

        return $this;
    }

    public function getDReception(): ?string
    {
        return $this->d_reception;
    }

    public function setDReception(string $d_reception): self
    {
        $this->d_reception = $d_reception;

        return $this;
    }

    public function getMontantHT(): ?string
    {
        return $this->montant_HT;
    }

    public function setMontantHT(string $montant_HT): self
    {
        $this->montant_HT = $montant_HT;

        return $this;
    }

    public function getValide(): ?string
    {
        return $this->valide;
    }

    public function setValide(string $valide): self
    {
        $this->valide = $valide;

        return $this;
    }

    public function getIsDisabled(): ?bool
    {
        return $this->is_disabled;
    }

    public function setIsDisabled(bool $is_disabled): self
    {
        $this->is_disabled = $is_disabled;

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

    public function getCourrier(): ?Courrier
    {
        return $this->courrier;
    }

    public function setCourrier(?Courrier $courrier): self
    {
        $this->courrier = $courrier;

        return $this;
    }
}
