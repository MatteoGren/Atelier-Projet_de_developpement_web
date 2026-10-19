<?php

declare(strict_types=1);

namespace nrv\adapters\config;

use DI\Container;
use DI\ContainerBuilder;
use nrv\adapters\persistence\PDOSpectacleRepository;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\usecases\ServiceSpectacle;
use PDO;
use Psr\Container\ContainerInterface;

class ContainerConfig
{
    public static function build(): Container
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            PDO::class => function (): PDO {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $_ENV['DB_HOST'] ?? 'nrv.db',
                    $_ENV['DB_PORT'] ?? '5432',
                    $_ENV['POSTGRES_DB']
                );
                return new PDO($dsn, $_ENV['POSTGRES_USER'], $_ENV['POSTGRES_PASSWORD'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            },

            // Ports -> implémentations (inversion de dépendances)
            SpectacleRepositoryInterface::class => function (ContainerInterface $c): SpectacleRepositoryInterface {
                return new PDOSpectacleRepository($c->get(PDO::class));
            },
            ServiceSpectacleInterface::class => function (ContainerInterface $c): ServiceSpectacleInterface {
                return new ServiceSpectacle($c->get(SpectacleRepositoryInterface::class));
            },
        ]);

        return $builder->build();
    }
}
