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

        $builder->addDefinitions([__DIR__ . '/settings.php']);
        $builder->addDefinitions([__DIR__ . '/services.php']);
        $builder->addDefinitions([__DIR__ . '/api.php']);

        return $builder->build();
    }
}
