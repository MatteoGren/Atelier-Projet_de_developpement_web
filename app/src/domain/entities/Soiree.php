<?php

declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;
use nrv\domain\exceptions\PlacesInsuffisantesException;

class Soiree
{
    // Tarif unique pour toutes les soirées (règle métier), non stocké en base.
    public const TARIF_NORMAL = 25.0;
    public const TARIF_REDUIT = 15.0;

    private string $id;
    private string $nom;
    private string $thematique;
    private DateTimeImmutable $date;
    private string $lieu;
    private int $capacite;
    private int $placesVendues;
    private float $recette;

    public function __construct(
        string $id,
        string $nom,
        string $thematique,
        DateTimeImmutable $date,
        string $lieu,
        int $capacite,
        int $placesVendues = 0,
        float $recette = 0
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->thematique = $thematique;
        $this->date = $date;
        $this->lieu = $lieu;
        $this->capacite = $capacite;
        $this->placesVendues = $placesVendues;
        $this->recette = $recette;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getThematique(): string
    {
        return $this->thematique;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function getCapacite(): int
    {
        return $this->capacite;
    }

    public function getPlacesVendues(): int
    {
        return $this->placesVendues;
    }

    public function getRecette(): float{
        return $this->recette;
    }

    public function setRecette(float $recette): float{
        return $this->recette = $recette;
    }

    public function placesDisponibles(): int
    {
        return $this->capacite - $this->placesVendues;
    }

    public function verifierPlaces(int $nbBillets): void
    {
        if ($nbBillets > $this->placesDisponibles()) {
            throw new PlacesInsuffisantesException($this->nom);
        }
    }

    public function getTarifs(): array
    {
        return [
            'normal' => self::TARIF_NORMAL,
            'reduit' => self::TARIF_REDUIT,
        ];
    }
}
