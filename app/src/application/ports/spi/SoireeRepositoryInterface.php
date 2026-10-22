<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Soiree;

interface SoireeRepositoryInterface
{
    /**
     * @return Soiree|null null si aucune soirée n'a cet identifiant
     */
    public function findById(string $id): ?Soiree;

    public function findAll(): array;
}
