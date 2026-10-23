<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use nrv\application\dtos\SoireeBackOfficeDTO;
use nrv\application\dtos\SoireeDetailDTO;
use nrv\application\dtos\CreerSoireeDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\validators\IdentifiantValidator;
use nrv\domain\entities\Soiree;
use Ramsey\Uuid\Uuid;

class ServiceSoiree implements ServiceSoireeInterface
{
    private SoireeRepositoryInterface $soireeRepository;
    private SpectacleRepositoryInterface $spectacleRepository;

    public function __construct(
        SoireeRepositoryInterface $soireeRepository,
        SpectacleRepositoryInterface $spectacleRepository
    ) {
        $this->soireeRepository = $soireeRepository;
        $this->spectacleRepository = $spectacleRepository;
    }

    public function afficherSoiree(string $id): SoireeDetailDTO
    {
        IdentifiantValidator::valider($id);

        $soiree = $this->soireeRepository->findById($id);
        if ($soiree === null) {
            throw new SoireeIntrouvableException($id);
        }

        $spectacles = $this->spectacleRepository->findBySoireeId($id);

        return SoireeDetailDTO::fromEntities($soiree, $spectacles);
    }

    public function creerSoiree(CreerSoireeDTO $dto): string
    {
        if ($dto->capacite <= 0) {
            throw new InvalidArgumentException("La capacité doit être strictement positive.");
        }

        $nbSpectacles = count($dto->spectacles);
        if ($nbSpectacles < 2 || $nbSpectacles > 3) {
            throw new InvalidArgumentException("Une soirée doit être associée à 2 ou 3 spectacles.");
        }

        try {
            $date = new DateTimeImmutable($dto->date);
        } catch (Exception) {
            throw new InvalidArgumentException("Format de date invalide.");
        }

        $id = class_exists(Uuid::class)
            ? Uuid::uuid4()->toString()
            : sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

        $soiree = new Soiree(
            $id,
            $dto->nom,
            $dto->thematique,
            $date,
            $dto->lieu,
            $dto->capacite,
            0
        );

        $this->soireeRepository->save($soiree);

        $this->spectacleRepository->associerSoireeAuxSpectacles($id, $dto->spectacles);

        return $id;
    }

    public function listerSoireeBackOffice(): array{
        $soirees = $this->soireeRepository->findAll();

        return array_map(function ($soiree){
            return new SoireeBackOfficeDTO(
                $soiree->getId(),
                $soiree->getNom(),
                $soiree->getPlacesVendues(),
                $soiree->getCapacite(),
                $soiree->getRecette()
            );
        }, $soirees);

    }
}
