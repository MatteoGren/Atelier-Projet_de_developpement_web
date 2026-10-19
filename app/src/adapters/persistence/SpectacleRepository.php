<?php

namespace nrv\adapters\persistence;

use nrv\application\ports\spi\SpectacleRepositoryInterface;


class SpectacleRepository implements SpectacleRepositoryInterface {

    public function __construct(
        private \PDO $pdo
    ) {}

    



}