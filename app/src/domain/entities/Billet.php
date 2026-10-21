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
    ) {
        $this->idBillet = $idBillet;
        $this->idCommande = $idCommande;
        $this->idSoiree = $idSoiree;
        $this->refBillet = $refBillet;
        $this->token = $token;
        $this->tarifReduit = $tarifReduit;
        $this->prixBillet = $prixBillet;
    }

    public function getIdBillet(): string { return $this->idBillet; }
    public function getIdCommande(): string { return $this->idCommande; }
    public function getIdSoiree(): string { return $this->idSoiree; }
    public function getRef(): string { return $this->refBillet; }
    public function getToken(): string { return $this->token; }
    public function isTarifReduit(): bool { return $this->tarifReduit; }
    public function getPrix(): float { return $this->prixBillet; }
    public function getCategorie(): string { return $this->tarifReduit ? 'reduit' : 'normal'; }


}