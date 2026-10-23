<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use DateTime;

interface ServiceSpectacleInterface
{
    public function listerSpectacles(): array;

    public function filtrerParDate(DateTime $date): array;

    public function filtrerParStyle(string $style): array;

    public function filtrer(DateTime $date, string $style): array;

    public function creerSpectacle(array $data): string;


}
