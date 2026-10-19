<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\application\dtos\SpectacleListeDTO;

interface ServiceSpectacleInterface
{
    /**
     * @return SpectacleListeDTO[]
     */
    public function listerSpectacles(): array;
}
