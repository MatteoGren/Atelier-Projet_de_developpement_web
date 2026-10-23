<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Factory\AppFactory;

use nrv\adapters\config\ContainerConfig;

use nrv\adapters\controllers\middlewares\Cors;
use nrv\adapters\controllers\middlewares\LectureJeton;

// Les tests fournissent leur propre $container (dépôts en mémoire, sans base ni nrv.env).
if (!isset($container)) {
    // nrv.env est monté en /var/nrv.env par docker compose
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../', 'nrv.env');
    $dotenv->load();
    $dotenv->required('JWT_SECRET')->notEmpty();

    $container = ContainerConfig::build();
}

$app = AppFactory::createFromContainer($container);
$app->addBodyParsingMiddleware();
// Ajouté avant le routing pour s'exécuter après lui : l'attribut idUtilisateur est posé avant les actions.
$app->add(LectureJeton::class);
$app->addRoutingMiddleware();

// Les détails techniques (fichier, ligne, requête SQL...) partent dans le journal du serveur, jamais au client.
$errorMiddleware = $app->addErrorMiddleware(false, true, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer(
    'application/json',
    function ($exception, $displayErrorDetails) {
        $message = $exception instanceof HttpException
            ? $exception->getMessage()
            : 'Erreur interne du serveur.';

        return json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    }
);

// Ajouté après le middleware d'erreurs pour l'englober : les réponses 400/404 ont aussi les en-têtes CORS.
$app->add(Cors::class);


$routes = require __DIR__ . '/api.php';
$routes($app);

$app->options(
    '/{routes:.+}',
    function (Request $request, Response $response): Response {
        return $response;
    }
);

return $app;
