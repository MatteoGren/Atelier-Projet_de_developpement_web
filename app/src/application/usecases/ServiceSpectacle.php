<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use DateTime;
use nrv\application\dtos\SpectacleListeDTO;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use Override;

class ServiceSpectacle implements ServiceSpectacleInterface
{
    private SpectacleRepositoryInterface $spectacleRepository;

    public function __construct(SpectacleRepositoryInterface $spectacleRepository)
    {
        $this->spectacleRepository = $spectacleRepository;
    }

    public function listerSpectacles(): array
    {
        return array_map(
            fn ($spectacle) => SpectacleListeDTO::fromEntity($spectacle),
            $this->spectacleRepository->findAll()
        );
    }

    
    public function filtrerParDate(DateTime $date): array
    {
        return array_map(
            function ($spectacle) {SpectacleListeDTO::fromEntity($spectacle);},
            $this->spectacleRepository->filtrerParDate($date)
        );
    }
}
