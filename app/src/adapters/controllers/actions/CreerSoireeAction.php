<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use InvalidArgumentException;
use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\dtos\CreerSoireeDTO;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreerSoireeAction
{
    private ServiceSoireeInterface $serviceSoiree;
    private UtilisateurRepositoryInterface $utilisateurRepository;

    public function __construct(
        ServiceSoireeInterface $serviceSoiree,
        UtilisateurRepositoryInterface $utilisateurRepository
    )
    {
        $this->serviceSoiree = $serviceSoiree;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        UtilisateurConnecte::exigerRole(
            $request,
            $this->utilisateurRepository,
            ['organisateur', 'administrateur']
        );

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