<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use nrv\adapters\config\ContainerConfig;

use nrv\adapters\controllers\middlewares\Cors;

// nrv.env est monté en /var/nrv.env par docker compose
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../', 'nrv.env');
$dotenv->load();

$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer(
    'application/json',
    function ($exception, $displayErrorDetails) {
        return json_encode([
            'message' => $exception->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
);

// Ajouté après le middleware d'erreurs pour l'englober : les réponses 400/404 ont aussi les en-têtes CORS.
$app->add(Cors::class);



//$routes = require __DIR__ . '/routes.php';
//$routes($app);



$app->options(
    '/{routes:.+}',
    function (Request $request, Response $response): Response {
        return $response;
    }
);

return $app;
