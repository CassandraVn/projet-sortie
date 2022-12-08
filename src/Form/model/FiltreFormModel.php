<?php

namespace App\Form\model;


use App\Entity\Campus;

class FiltreFormModel
{
    private Campus|null $campus = null;

    private ?string $nomSortie = null;

    private ?\DateTime $dateDepuis = null;

    private ?\DateTime $dateUntil = null;

    private ?bool $organisateur = null;

    private ?bool $inscrit = null;

    private ?bool $pasInscrit = null;

    private ?bool $passees = null;

    public function __construct()
    {

    }

    /**
     * @return int|null
     */
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    /**
     * @param Campus|null $campus
     */
    public function setCampus(Campus|null $campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return string|null
     */
    public function getNomSortie(): ?string
    {
        return $this->nomSortie;
    }

    /**
     * @param string|null $nomSortie
     */
    public function setNomSortie(?string $nomSortie): void
    {
        $this->nomSortie = $nomSortie;
    }

    /**
     * @return Date|null
     */
    public function getDateDepuis(): ?\DateTime
    {
        return $this->dateDepuis;
    }

    /**
     * @param Date|null $dateDepuis
     */
    public function setDateDepuis(?\DateTime $dateDepuis): void
    {
        $this->dateDepuis = $dateDepuis;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateUntil(): ?\DateTime
    {
        return $this->dateUntil;
    }

    /**
     * @param \DateTime|null $dateUntil
     */
    public function setDateUntil(?\DateTime $dateUntil): void
    {
        $this->dateUntil = $dateUntil;
    }

    /**
     * @return bool|null
     */
    public function getOrganisateur(): ?bool
    {
        return $this->organisateur;
    }

    /**
     * @param bool|null $organisateur
     */
    public function setOrganisateur(?bool $organisateur): void
    {
        $this->organisateur = $organisateur;
    }

    /**
     * @return bool|null
     */
    public function getInscrit(): ?bool
    {
        return $this->inscrit;
    }

    /**
     * @param bool|null $inscrit
     */
    public function setInscrit(?bool $inscrit): void
    {
        $this->inscrit = $inscrit;
    }

    /**
     * @return bool|null
     */
    public function getPasInscrit(): ?bool
    {
        return $this->pasInscrit;
    }

    /**
     * @param bool|null $pasInscrit
     */
    public function setPasInscrit(?bool $pasInscrit): void
    {
        $this->pasInscrit = $pasInscrit;
    }

    /**
     * @return bool|null
     */
    public function getPassees(): ?bool
    {
        return $this->passees;
    }

    /**
     * @param bool|null $passees
     */
    public function setPassees(?bool $passees): void
    {
        $this->passees = $passees;
    }


}


