<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\application\dtos\SoireeDetailDTO;
use nrv\application\dtos\CreerSoireeDTO;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;

interface ServiceSoireeInterface
{
    /**
     * @throws IdentifiantInvalideException si l'identifiant n'est pas un UUID
     * @throws SoireeIntrouvableException si la soirée n'existe pas
     */
    public function afficherSoiree(string $id): SoireeDetailDTO;

    public function creerSoiree(CreerSoireeDTO $dto): string;

}
