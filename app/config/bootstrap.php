<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use toubilib\adapters\config\ContainerConfig;
use toubilib\adapters\controllers\middlewares\Cors;

// Actions 
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;

use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetAllPraticienAction;
use toubilib\adapters\controllers\actions\GetCreneauxOccupesPraticienAction;

$dotenv = \Dotenv\Dotenv::createImmutable(
    __DIR__ . '/../../',
    'toubilib.env'
);
$dotenv->load();

$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);
$app->addBodyParsingMiddleware();
$app->add(Cors::class);
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer(
    'application/json',
    function ($exception, $displayErrorDetails) {
        return json_encode([
            'message' => $exception->getMessage()
        ]);
    }
);

$app->group('/rendezvous', function (\Slim\Routing\RouteCollectorProxy $group) {

    $group->post('[/]', CreerRendezVousAction::class )->setName('CreerRendezVous');
    $group->post('/{id}/annuler[/]', AnnulerRendezVousAction::class )->setName('AnnulerRendezVous');
    $group->post('/{id}/honorer[/]', HonorerRendezVousAction::class )->setName('HonorerRendezVous');
    $group->post('/{id}/ignorer[/]', IgnorerRendezVousAction::class )->setName('IgnorerRendezVous');

    $group->get('/{id}[/]', GetRendezVousAction::class )->setName('GetRendezVous');
});

$app->group('/praticiens', function (\Slim\Routing\RouteCollectorProxy $group) {

    $group->get('[/]', GetAllPraticienAction::class )->setName('GetAllPraticiens');
    $group->get('/{id}[/]', GetPraticienAction::class )->setName('GetPraticien');
    $group->get('/{id}/agenda[/]', GetPraticienAgendaAction::class )->setName('GetPraticienAgenda');
    $group->get('/{id}/creneaux-occupes', GetCreneauxOccupesPraticienAction::class)->setName('GetCreneauxOccupesPraticien');

    // 4. Consulter l’agenda d’un praticien pour une période donnée : horaires des rendez-vous, patients concernés, motifs de visite

    // 7. Lister les créneaux de RDV déjà occupés pour un praticien sur une période donnée (date de
    // début, date de fin)

});

$app->options(
    '/{routes:.+}',
    function (Request $request, Response $response): Response {
        return $response;
    }
);

$routeParser = $app->getRouteCollector()->getRouteParser();

return $app;
