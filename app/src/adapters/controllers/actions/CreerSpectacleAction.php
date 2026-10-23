<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use DateTimeImmutable;
use Exception;
use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

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

        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new HttpBadRequestException($request, 'Corps invalide : un objet JSON est attendu.');
        }

        foreach (['titre', 'artistes', 'style', 'horaire'] as $champ) {
            if (!is_string($body[$champ] ?? null) || trim($body[$champ]) === '') {
                throw new HttpBadRequestException($request, 'Corps invalide : titre, artistes, style et horaire (texte non vide) sont attendus.');
            }
        }

        try {
            new DateTimeImmutable($body['horaire']);
        } catch (Exception) {
            throw new HttpBadRequestException($request, 'Horaire invalide.');
        }

        $data = [
            'soiree_id'    => is_string($body['soiree_id'] ?? null) && $body['soiree_id'] !== '' ? $body['soiree_id'] : null,
            'titre'        => trim($body['titre']),
            'artistes'     => trim($body['artistes']),
            'description'  => $body['description'] ?? null,
            'images'       => !empty($body['images']) ? array_map('trim', explode(';', (string)$body['images'])) : [],
            'chemin_video' => $body['chemin_video'] ?? null,
            'horaire'      => $body['horaire'],
            'style'        => trim($body['style'])
        ];

        $spectacleId = $this->serviceSpectacle->creerSpectacle($data);

        // Pas de route GET /spectacles/{id} : pas d'en-tête Location, le spectacle est rendu dans la réponse.
        $liens = ['collection' => ['href' => '/spectacles']];
        if ($data['soiree_id'] !== null) {
            $liens['soiree'] = ['href' => '/soirees/' . $data['soiree_id']];
        }

        $donnees = [
            'type' => 'resource',
            'spectacle' => array_merge(['id' => $spectacleId], $data),
            'links' => $liens,
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}
