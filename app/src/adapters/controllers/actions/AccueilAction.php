<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Point d'entrée de l'API : le client découvre les autres routes en suivant ces liens.
class AccueilAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $donnees = [
            'type' => 'resource',
            'links' => [
                'self' => ['href' => '/'],
                'spectacles' => ['href' => '/spectacles'],
                'spectaclesFiltres' => ['href' => '/spectacles{?date,style}', 'templated' => true],
                'soirees' => ['href' => '/soirees'],
                'commandes' => ['href' => '/commandes'],
                'billets' => ['href' => '/billets'],
                'inscription' => ['href' => '/utilisateurs'],
                'connexion' => ['href' => '/sessions'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
