<?php

declare(strict_types=1);

namespace nrv\application\dtos;

// Entrée = données de carte transmises au paiement (simulé : jamais enregistrées)
final class PaiementDTO
{
    public readonly string $idCommande;
    public readonly string $idUtilisateur;
    public readonly string $numeroCarte;
    public readonly string $dateExpiration;
    public readonly string $code;

    public function __construct(
        string $idCommande,
        string $idUtilisateur,
        string $numeroCarte,
        string $dateExpiration,
        string $code
    ) {
        $this->idCommande = $idCommande;
        $this->idUtilisateur = $idUtilisateur;
        $this->numeroCarte = $numeroCarte;
        $this->dateExpiration = $dateExpiration;
        $this->code = $code;
    }
}
