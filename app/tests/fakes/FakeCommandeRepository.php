<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\domain\entities\Commande;

// Repository en mémoire : permet de tester le service sans base de données.
class FakeCommandeRepository implements CommandeRepositoryInterface
{
    /** @var Commande[] indexées par identifiant */
    public array $commandes = [];

    public function save(Commande $commande): void
    {
        $this->commandes[$commande->getIdCommande()] = $commande;
    }

    public function findById(string $id): ?Commande
    {
        return $this->commandes[$id] ?? null;
    }

    public function enregistrerPaiement(Commande $commande): void
    {
        $this->commandes[$commande->getIdCommande()] = $commande;
    }
}
