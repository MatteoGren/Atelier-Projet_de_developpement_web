<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use DateTimeImmutable;
use DI\ContainerBuilder;
use nrv\adapters\auth\GestionnaireJwt;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\domain\entities\Soiree;
use nrv\domain\entities\Spectacle;
use nrv\domain\entities\Utilisateur;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

// L'application Slim complète (routes, middlewares, actions, services) branchée sur des dépôts en mémoire.
// Seule la base de données est remplacée : les requêtes traversent tout le reste comme en production.
class ApiEnMemoire
{
    public const SECRET_JWT = 'secret-de-test-assez-long-pour-hs256-0123456789';
    public const ORIGINE_FRONT = 'http://localhost:31000';

    public FakeUtilisateurRepository $utilisateurs;
    public FakeSoireeRepository $soirees;
    public FakeSpectacleRepository $spectacles;
    public FakeCommandeRepository $commandes;
    public GestionnaireJwt $jwt;
    private App $app;

    public function __construct()
    {
        $hash = password_hash('secret123', PASSWORD_DEFAULT);
        $this->utilisateurs = new FakeUtilisateurRepository([
            new Utilisateur(\CLIENT, 'Martin', 'Léa', 'lea@example.com', '0600000000', $hash, 'client'),
            new Utilisateur(\AUTRE_CLIENT, 'Durand', 'Paul', 'paul@example.com', '0611111111', $hash, 'client'),
            new Utilisateur(\ORGANISATEUR, 'Org', 'Anne', 'orga@example.com', '0622222222', $hash, 'organisateur'),
            new Utilisateur(\ADMINISTRATEUR, 'Admin', 'Marc', 'admin@example.com', '0633333333', $hash, 'administrateur'),
        ]);
        $this->soirees = new FakeSoireeRepository([
            new Soiree(\uneSoiree(1), 'Ouverture Électrique', 'Classic rock', new DateTimeImmutable('2027-06-04 20:00'), 'Place Stanislas', 800, 3, 65.0),
            new Soiree(\uneSoiree(2), 'Blues sur la Meurthe', 'Blues', new DateTimeImmutable('2027-06-06 20:00'), 'Péniche', 500),
            new Soiree(\uneSoiree(3), 'Soirée complète', 'Rock', new DateTimeImmutable('2027-06-08 20:00'), 'Nancy', 10, 10),
        ]);
        $this->spectacles = new FakeSpectacleRepository([
            new Spectacle('spectacle-1', \uneSoiree(1), 'Highway Night', 'Route 54', null, ['img/highway.jpg'], null, new DateTimeImmutable('2027-06-04 20:30'), 'rock'),
            new Spectacle('spectacle-2', \uneSoiree(2), 'Blues de la rivière', 'Delta', null, [], null, new DateTimeImmutable('2027-06-06 21:00'), 'blues'),
            new Spectacle('libre-1', null, 'Libre 1', 'Groupe', null, [], null, new DateTimeImmutable('2027-06-20 20:30'), 'metal'),
            new Spectacle('libre-2', null, 'Libre 2', 'Groupe', null, [], null, new DateTimeImmutable('2027-06-20 22:00'), 'metal'),
        ]);
        $this->commandes = new FakeCommandeRepository();

        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/../../config/services.php');
        $builder->addDefinitions([
            'jwt.secret' => self::SECRET_JWT,
            'cors.origines' => [self::ORIGINE_FRONT],
            UtilisateurRepositoryInterface::class => $this->utilisateurs,
            SoireeRepositoryInterface::class => $this->soirees,
            SpectacleRepositoryInterface::class => $this->spectacles,
            CommandeRepositoryInterface::class => $this->commandes,
            BilletRepositoryInterface::class => new FakeBilletRepository($this->commandes),
        ]);
        $container = $builder->build();
        $this->jwt = $container->get(GestionnaireJwt::class);

        // bootstrap.php garde le $container fourni au lieu de lire nrv.env et la base.
        $this->app = require __DIR__ . '/../../config/bootstrap.php';
    }

    public function get(string $uri, ?string $utilisateur = null): ResponseInterface
    {
        return $this->envoyer('GET', $uri, null, $utilisateur);
    }

    public function post(string $uri, mixed $corps, ?string $utilisateur = null): ResponseInterface
    {
        return $this->envoyer('POST', $uri, $corps, $utilisateur);
    }

    public function envoyer(string $methode, string $uri, mixed $corps = null, ?string $utilisateur = null, array $entetes = []): ResponseInterface
    {
        $requete = (new ServerRequestFactory())->createServerRequest($methode, $uri);
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $parametres);
        $requete = $requete->withQueryParams($parametres);

        if ($corps !== null) {
            $requete = $requete
                ->withHeader('Content-Type', 'application/json')
                ->withBody((new StreamFactory())->createStream(is_string($corps) ? $corps : json_encode($corps)));
        }
        if ($utilisateur !== null) {
            $requete = $requete->withHeader('Authorization', 'Bearer ' . $this->jwt->creer($utilisateur, 'client'));
        }
        foreach ($entetes as $nom => $valeur) {
            $requete = $requete->withHeader($nom, $valeur);
        }

        return $this->app->handle($requete);
    }
}
