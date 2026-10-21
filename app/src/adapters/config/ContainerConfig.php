<?php

declare(strict_types=1);

namespace nrv\adapters\config;

use DI\Container;
use DI\ContainerBuilder;
use nrv\adapters\persistence\PDOCommandeRepository;
use nrv\adapters\persistence\PDOSoireeRepository;
use nrv\adapters\persistence\PDOSpectacleRepository;
use nrv\adapters\persistence\UtilisateurRepository;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\usecases\ServiceCommande;
use nrv\application\usecases\ServiceSoiree;
use nrv\application\usecases\ServiceSpectacle;
use nrv\application\usecases\ServiceUtilisateur;
use PDO;
use Psr\Container\ContainerInterface;

use nrv\adapters\controllers\actions\RegisterAction;
use nrv\adapters\controllers\actions\ConnexionAction;

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
            SoireeRepositoryInterface::class => function (ContainerInterface $c): SoireeRepositoryInterface {
                return new PDOSoireeRepository($c->get(PDO::class));
            },
            ServiceSoireeInterface::class => function (ContainerInterface $c): ServiceSoireeInterface {
                return new ServiceSoiree(
                    $c->get(SoireeRepositoryInterface::class),
                    $c->get(SpectacleRepositoryInterface::class)
                );
            },


            //ajout pour des test perso 
            UtilisateurRepositoryInterface::class => function (ContainerInterface $c): UtilisateurRepositoryInterface {
                return new UtilisateurRepository($c->get(PDO::class));
            },
            ServiceUtilisateurInterface::class => function (ContainerInterface $c): ServiceUtilisateurInterface {
                return new ServiceUtilisateur($c->get(UtilisateurRepositoryInterface::class));
            },

            CommandeRepositoryInterface::class => function (ContainerInterface $c): CommandeRepositoryInterface {
                return new PDOCommandeRepository($c->get(PDO::class));
            },
            ServiceCommandeInterface::class => function (ContainerInterface $c): ServiceCommandeInterface {
                return new ServiceCommande(
                    $c->get(SoireeRepositoryInterface::class),
                    $c->get(CommandeRepositoryInterface::class)
                );
            },

        ]);

        return $builder->build();
    }
}
