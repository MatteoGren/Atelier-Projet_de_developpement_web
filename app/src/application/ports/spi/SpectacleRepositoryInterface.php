<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Spectacle;

interface SpectacleRepositoryInterface
{
    /**
     * @return Spectacle[] triés par horaire croissant
     */
    public function findAll(): array;
}
