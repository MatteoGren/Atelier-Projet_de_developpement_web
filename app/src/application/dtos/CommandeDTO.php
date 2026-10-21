<?php

declare(strict_types=1);

namespace nrv\application\dtos;

use nrv\domain\entities\Commande;

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

    public static function fromEntity(Commande $commande): self
    {
        return new self(
            $commande->getIdCommande(),
            $commande->getStatut(),
            $commande->getMontantTotal(),
            count($commande->getBillets())
        );
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