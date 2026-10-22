<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use InvalidArgumentException;
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
        // 1. Récupération sécurisée du body (gère form-data et payload JSON)
        $body = (array) $request->getParsedBody();
        if (empty($body)) {
            $json = (string) $request->getBody();
            $body = json_decode($json, true) ?? [];
        }

        try {
            // Les tarifs sont fixes par la règle métier de Soiree.
            $dto = new CreerSoireeDTO(
                nom: (string) ($body['nom'] ?? ''),
                thematique: (string) ($body['thematique'] ?? ''),
                date: (string) ($body['date'] ?? ''),
                lieu: (string) ($body['lieu'] ?? ''),
                capacite: (int) ($body['capacite'] ?? 0),
                spectacles: (array) ($body['spectacles'] ?? [])
            );

            // 3. Appel du service (qui insère la soirée et fait l'UPDATE des spectacles)
            $id = $this->serviceSoiree->creerSoiree($dto);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'id' => $id
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);

        } catch (InvalidArgumentException $e) {
            // Gestion propre des erreurs de validation (ex: moins de 2 spectacles cochés)
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}