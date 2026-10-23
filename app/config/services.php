<?php

use nrv\adapters\auth\GestionnaireJwt;
use nrv\adapters\controllers\middlewares\Cors;
use nrv\adapters\persistence\PDOBilletRepository;
use nrv\adapters\persistence\PDOCommandeRepository;
use nrv\adapters\persistence\PDOSoireeRepository;
use nrv\adapters\persistence\PDOSpectacleRepository;
use nrv\adapters\persistence\UtilisateurRepository;
use nrv\application\ports\api\ServiceBilletInterface;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\usecases\ServiceBillet;
use nrv\application\usecases\ServiceCommande;
use nrv\application\usecases\ServiceSoiree;
use nrv\application\usecases\ServiceSpectacle;
use nrv\application\usecases\ServiceUtilisateur;
use Psr\Container\ContainerInterface;

    return [
         PDO::class => function (ContainerInterface $c): PDO {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $c->get('host'),
                    $c->get('port'),
                    $c->get('database')
                );
                return new PDO($dsn, $c->get('username'), $c->get('password'), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]   );
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
            BilletRepositoryInterface::class => function (ContainerInterface $c): BilletRepositoryInterface {
                return new PDOBilletRepository($c->get(PDO::class));
            },
            ServiceBilletInterface::class => function (ContainerInterface $c): ServiceBilletInterface {
                return new ServiceBillet(
                    $c->get(BilletRepositoryInterface::class),
                    $c->get(CommandeRepositoryInterface::class),
                    $c->get(SoireeRepositoryInterface::class),
                    $c->get(UtilisateurRepositoryInterface::class)
                );
            },
            GestionnaireJwt::class => function (ContainerInterface $c): GestionnaireJwt {
                return new GestionnaireJwt($c->get('jwt.secret'));
            },
            Cors::class => function (ContainerInterface $c): Cors {
                return new Cors($c->get('cors.origines'));
            }
            

    ];
