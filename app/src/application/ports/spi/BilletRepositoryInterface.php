<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Billet;

interface BilletRepositoryInterface
{
    /**
     * @return Billet|null null si aucun billet n'a ce token
     */
    public function findByToken(string $token): ?Billet;
}
