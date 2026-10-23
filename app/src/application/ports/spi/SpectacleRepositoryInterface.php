<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use DateTime;
use nrv\domain\entities\Spectacle;

interface SpectacleRepositoryInterface
{
    public function findAll(): array;

    public function findBySoireeId(string $soireeId): array;

    public function filtrerParDate(DateTime $date): array;

    public function filtrerParStyle(string $style): array;

    public function filtrer(DateTime $date, string $style): array;

    public function save(Spectacle $spectacle): string;

    public function associerSoireeAuxSpectacles(string $soireeId, array $spectacleIds): void;

}
