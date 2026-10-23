<?php

declare(strict_types=1);

namespace nrv\application\dtos;

final class CreerSoireeDTO
{
    public readonly string $nom;
    public readonly string $thematique;
    public readonly string $date;
    public readonly string $lieu;
    public readonly int $capacite;
    /** @var string[] */
    public readonly array $spectacles;

    public function __construct(
        string $nom,
        string $thematique,
        string $date,
        string $lieu,
        int $capacite,
        array $spectacles = []
    ) {
        $this->nom = $nom;
        $this->thematique = $thematique;
        $this->date = $date;
        $this->lieu = $lieu;
        $this->capacite = $capacite;
        $this->spectacles = $spectacles;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['nom'] ?? '',
            $data['thematique'] ?? '',
            $data['date'] ?? '',
            $data['lieu'] ?? '',
            (int) ($data['capacite'] ?? 0),
            $data['spectacles'] ?? []
        );
    }
}