<?php

declare(strict_types=1);

namespace nrv\adapters\config;

use DI\Container;
use DI\ContainerBuilder;


class ContainerConfig
{
    public static function build(): Container
    {
        $builder = new ContainerBuilder();

        // Fichiers de définitions rangés dans app/config ; les routes (api.php) sont chargées par bootstrap.php.
        $builder->addDefinitions(__DIR__ . '/../../../config/settings.php');
        $builder->addDefinitions(__DIR__ . '/../../../config/services.php');

        return $builder->build();
    }
}
