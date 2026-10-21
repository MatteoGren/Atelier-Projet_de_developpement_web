<?php

declare(strict_types=1);

namespace nrv\application\dtos;

// Sortie = la commande telle qu'elle est renvoyée au client
final class CommandeDTO
{
    public readonly string $id;
    public readonly string $statut;
    public readonly float $montantTotal;
    public readonly int $nbBillets;

    public function __construct(string $id, string $statut, float $montantTotal, int $nbBillets
    ) {
        $this->id = $id;
        $this->statut = $statut;
        $this->montantTotal = $montantTotal;
        $this->nbBillets = $nbBillets;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'statut' => $this->statut,
            'montantTotal' => $this->montantTotal,
            'nbBillets' => $this->nbBillets,
        ];
    }
}