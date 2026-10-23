<?php

declare(strict_types=1);

namespace nrv\application\dtos;

final class SoireeBackOfficeDTO{



    public function __construct(
        public string $id,
        public string $nom,
        public int $placesVendues,
        public int $capacite,
        public float $recette
    ){}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'placesVendues' => $this->placesVendues,
            'capacite' => $this->capacite,
            'recette' => $this->recette
        ];
    }
}