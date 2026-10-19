<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceSpectacleInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetSpectaclesAction
{
    private ServiceSpectacleInterface $serviceSpectacle;

    public function __construct(ServiceSpectacleInterface $serviceSpectacle)
    {
        $this->serviceSpectacle = $serviceSpectacle;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $spectacles = $this->serviceSpectacle->listerSpectacles();

        $donnees = [
            'type' => 'collection',
            'count' => count($spectacles),
            'spectacles' => array_map(fn ($dto) => [
                'spectacle' => $dto->toArray(),
                'links' => [
                    'soiree' => ['href' => '/soirees/' . $dto->soireeId],
                ],
            ], $spectacles),
            'links' => [
                'self' => ['href' => '/spectacles'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
