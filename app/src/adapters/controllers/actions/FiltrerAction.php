<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use DateTime;
use nrv\application\ports\api\ServiceSpectacleInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class FiltrerAction
{
    private ServiceSpectacleInterface $service;

    public function __construct(ServiceSpectacleInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $parametres = $request->getQueryParams();
        $requestDate = trim((string) ($parametres['date'] ?? ''));
        $requestStyleMusique = trim((string) ($parametres['style'] ?? ''));

        // Paramètre vide = filtre non utilisé (« Toutes les dates », « Tous les styles »).
        $date = $requestDate === '' ? null : $this->lireDate($request, $requestDate);

        if ($date !== null && $requestStyleMusique !== '') {
            $dtos = $this->service->filtrer($date, $requestStyleMusique);
        } elseif ($date !== null) {
            $dtos = $this->service->filtrerParDate($date);
        } elseif ($requestStyleMusique !== '') {
            $dtos = $this->service->filtrerParStyle($requestStyleMusique);
        } else {
            $dtos = $this->service->listerSpectacles();
        }

        $donnees = [
            'type' => 'collection',
            'count' => count($dtos),
            'spectacles' => array_map(fn ($dto) => [
                'spectacle' => $dto->toArray(),
                'links' => [
                    'soiree' => ['href' => '/soirees/' . $dto->soireeId],
                ],
            ], $dtos),
            'links' => [
                'self' => ['href' => '/spectacles/filtre?' . http_build_query(array_filter([
                    'date' => $requestDate,
                    'style' => $requestStyleMusique,
                ]))],
                'spectacles' => ['href' => '/spectacles'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // Date attendue au format AAAA-MM-JJ, sinon erreur 400 (rendue en JSON par Slim).
    private function lireDate(Request $request, string $texte): DateTime
    {
        $date = DateTime::createFromFormat('!Y-m-d', $texte);
        if ($date === false || $date->format('Y-m-d') !== $texte) {
            throw new HttpBadRequestException($request, 'Date invalide : format attendu AAAA-MM-JJ.');
        }
        return $date;
    }
}
