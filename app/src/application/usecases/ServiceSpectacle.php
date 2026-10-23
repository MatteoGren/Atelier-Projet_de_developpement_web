<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use DateTime;
use nrv\application\dtos\SpectacleListeDTO;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\domain\entities\Spectacle;
use Ramsey\Uuid\Uuid;

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
            function ($spectacle) {return SpectacleListeDTO::fromEntity($spectacle);},
            $this->spectacleRepository->filtrerParDate($date)
        );
    }

    public function filtrerParStyle(string $style): array
    {
        return array_map(
            function ($spectacle) {return SpectacleListeDTO::fromEntity($spectacle);},
            $this->spectacleRepository->filtrerParStyle($style)
        );
    }

    public function filtrer(DateTime $date, string $style): array {
        return array_map(
            function ($spectacle) {return SpectacleListeDTO::fromEntity($spectacle);},
            $this->spectacleRepository->filtrer($date,$style)
        );
    }


    public function creerSpectacle(array $data): string
    {

        $soireeId = $data['soiree_id'] ?? null;

        $id = $data['id'] ?? (function_exists('uuid_create') ? uuid_create() : sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)));

        $spectacle = new Spectacle(
            $id,
            $soireeId,
            $data['titre'],
            $data['artistes'],
            $data['description'] ?? null,
            $data['images'] ?? [],
            $data['chemin_video'] ?? null,
            new \DateTimeImmutable($data['horaire'] ?? 'now'),
            $data['style']
        );

        return $this->spectacleRepository->save($spectacle);
    }

}
