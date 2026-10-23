<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\domain\entities\Billet;

// Repository en mémoire : les billets sont lus dans les commandes du faux dépôt de commandes.
class FakeBilletRepository implements BilletRepositoryInterface
{
    public function __construct(private FakeCommandeRepository $commandes)
    {
    }

    public function findByToken(string $token): ?Billet
    {
        foreach ($this->commandes->commandes as $commande) {
            foreach ($commande->getBillets() as $billet) {
                if ($billet->getToken() === $token) {
                    return $billet;
                }
            }
        }
        return null;
    }

    public function findPayesByUtilisateur(string $idUtilisateur): array
    {
        $billets = [];
        foreach ($this->commandes->commandes as $commande) {
            if ($commande->getIdUtilisateur() === $idUtilisateur && $commande->estPayee()) {
                array_push($billets, ...$commande->getBillets());
            }
        }
        return $billets;
    }
}
