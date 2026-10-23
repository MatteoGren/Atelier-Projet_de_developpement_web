<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Billet;

interface BilletRepositoryInterface
{
    public function findByToken(string $token): ?Billet;

    public function findPayesByUtilisateur(string $idUtilisateur): array;
}
