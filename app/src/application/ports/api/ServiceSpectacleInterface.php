<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use DateTime;
use nrv\application\dtos\SpectacleListeDTO;

interface ServiceSpectacleInterface
{
    /**
     * @return SpectacleListeDTO[]
     */
    public function listerSpectacles(): array;

    public function filtrerParDate(DateTime $date): array;

    public function filtrerParStyle(string $style): array;

    public function filtrer(DateTime $date, string $style): array;

    public function creerSpectacle(array $data): string;


}
