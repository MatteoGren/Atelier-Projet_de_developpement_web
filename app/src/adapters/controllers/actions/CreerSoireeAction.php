<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\dtos\CreerSoireeDTO;
use nrv\application\ports\api\ServiceSoireeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreerSoireeAction
{
    private ServiceSoireeInterface $serviceSoiree;

    public function __construct(ServiceSoireeInterface $serviceSoiree)
    {
        $this->serviceSoiree = $serviceSoiree;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $dto = new CreerSoireeDTO(
            nom: (string) ($body['nom'] ?? ''),
            thematique: (string) ($body['thematique'] ?? ''),
            date: (string) ($body['date'] ?? ''),
            lieu: (string) ($body['lieu'] ?? ''),
            capacite: (int) ($body['capacite'] ?? 0)
        );

        $id = $this->serviceSoiree->creerSoiree($dto);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'id' => $id
        ]));

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}