<?php

namespace nrv\domain\entities;



class Billet {

    public function __construct(
        private string $idBillet,
        private string $idCommande,
        private string $idSoiree,
        private string $refBillet,
        private string $token,
        private bool $tarifReduit,
        private float $prixBillet,
    ) {}

    


}