<?php

namespace nrv\adapters\persistence;

use nrv\application\ports\spi\SoireeRepositoryInterface;


class SoireeRepository implements SoireeRepositoryInterface {

    public function __construct(
        private \PDO $pdo
    ) {}





}