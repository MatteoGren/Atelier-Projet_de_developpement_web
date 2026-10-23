<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\application\dtos\SoireeDetailDTO;
use nrv\application\dtos\CreerSoireeDTO;

interface ServiceSoireeInterface
{
    public function afficherSoiree(string $id): SoireeDetailDTO;

    public function creerSoiree(CreerSoireeDTO $dto): string;

    public function listerSoireeBackOffice(): array;
}
