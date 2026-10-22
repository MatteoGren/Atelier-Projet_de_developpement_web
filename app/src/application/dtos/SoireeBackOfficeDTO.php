<?php

declare(strict_types=1);

namespace nrv\application\dtos;

final class SoireeBackOfficeDTO{

    private string $nom;
    private int $placesVendues;
    private int $capacite;
    private float $recette;

    public function __construct(
        string $nom,
        int $placesVendues,
        int $capacite,
        float $recette
    ){}

    public function toArray(): array
    {
        return [
            'nom' => $this->nom,
            'placesVendues' => $this->placesVendues,
            'capacite' => $this->capacite,
            'recette' => $this->recette
        ];
    }
}