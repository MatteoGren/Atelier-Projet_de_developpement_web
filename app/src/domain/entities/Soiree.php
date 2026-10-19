<?php

namespace nrv\domain\entities;


class soiree {

    public function __construct(
        private string $idSoiree,
        private string $nomSoiree,
        private string $thematique,
        private DateTime $dateSoiree,
        private string $lieuSoiree,
        private string $capaciteSoiree,
        private float $prixSoiree,
    ) {}



}