<?php

declare(strict_types=1);

namespace nrv\application\dtos;

final class CreerSoireeDTO
{
    public function __construct(
        public readonly string $nom,
        public readonly string $thematique,
        public readonly string $date,
        public readonly string $lieu,
        public readonly int $capacite
    ) {}
}