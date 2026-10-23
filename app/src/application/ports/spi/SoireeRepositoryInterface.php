<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Soiree;

interface SoireeRepositoryInterface
{
    public function findById(string $id): ?Soiree;

    public function save(Soiree $soiree): void;

    public function findAll(): array;

    public function chargerRecette(Soiree $soiree): float;
}
