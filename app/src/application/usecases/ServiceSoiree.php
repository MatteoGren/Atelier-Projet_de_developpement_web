<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dtos\SoireeDetailDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\validators\IdentifiantValidator;

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
}
