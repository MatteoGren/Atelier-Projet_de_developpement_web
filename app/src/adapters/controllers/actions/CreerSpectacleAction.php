<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreerSpectacleAction
{
    private ServiceSpectacleInterface $serviceSpectacle;
    private UtilisateurRepositoryInterface $utilisateurRepository;

    public function __construct(
        ServiceSpectacleInterface $serviceSpectacle,
        UtilisateurRepositoryInterface $utilisateurRepository
    )
    {
        $this->serviceSpectacle = $serviceSpectacle;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        UtilisateurConnecte::exigerRole(
            $request,
            $this->utilisateurRepository,
            ['organisateur', 'administrateur']
        );

        $body = (array)$request->getParsedBody();

        // Transformation et validation minimale
        $data = [
            'soiree_id'    => $body['soiree_id'] ?? null,
            'titre'        => trim($body['titre'] ?? ''),
            'artistes'     => trim((string)($body['artistes'] ?? '')),
            'description'  => $body['description'] ?? null,
            'images'       => !empty($body['images']) ? array_map('trim', explode(';', (string)$body['images'])) : [],
            'chemin_video' => $body['chemin_video'] ?? null,
            'horaire'      => (string) ($body['horaire'] ?? ''),
            'style'        => trim($body['style'] ?? '')
        ];

        try {
            $spectacleId = $this->serviceSpectacle->creerSpectacle($data);

            $payload = json_encode(['success' => true, 'id' => $spectacleId]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withStatus(201);
        } catch (\Throwable $e) {
            $payload = json_encode(['success' => false, 'error' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withStatus(400);
        }
    }
}