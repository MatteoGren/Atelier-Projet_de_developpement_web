<?php

declare(strict_types=1);

namespace nrv\application\dtos;

// Entrée = prix calculés par le métier (: le client n'envoie que des quantités)
final class CreerCommandeDTO
{
    public readonly string $idUtilisateur;
    public readonly array $lignes;

    public function __construct(string $idUtilisateur, array $lignes
    ) {
        $this->idUtilisateur = $idUtilisateur;
        $this->lignes = $lignes;
    }
}