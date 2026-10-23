<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Commande;

interface CommandeRepositoryInterface
{
    public function save(Commande $commande): void;

    public function findById(string $id): ?Commande;

    public function enregistrerPaiement(Commande $commande): void;
}
