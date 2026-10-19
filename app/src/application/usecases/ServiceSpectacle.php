<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dtos\SpectacleListeDTO;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;

class ServiceSpectacle implements ServiceSpectacleInterface
{
    public function __construct(private SpectacleRepositoryInterface $spectacleRepository)
    {
    }

    public function listerSpectacles(): array
    {
        return array_map(
            fn ($spectacle) => SpectacleListeDTO::fromEntity($spectacle),
            $this->spectacleRepository->findAll()
        );
    }
}
