<?php

declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;

class Soiree
{
    // Tarif unique pour toutes les soirées (règle métier), non stocké en base.
    public const TARIF_NORMAL = 25.0;
    public const TARIF_REDUIT = 15.0;

    private string $id;
    private string $nom;
    private string $thematique;
    /** date et horaire de début */
    private DateTimeImmutable $date;
    private string $lieu;
    private int $capacite;

    public function __construct(
        string $id,
        string $nom,
        string $thematique,
        DateTimeImmutable $date,
        string $lieu,
        int $capacite
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->thematique = $thematique;
        $this->date = $date;
        $this->lieu = $lieu;
        $this->capacite = $capacite;
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

    /**
     * @return array{normal: float, reduit: float}
     */
    public function getTarifs(): array
    {
        return [
            'normal' => self::TARIF_NORMAL,
            'reduit' => self::TARIF_REDUIT,
        ];
    }
}
