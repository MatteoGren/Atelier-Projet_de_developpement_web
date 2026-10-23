<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Exception;
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
        $tabSpectacles = [];
        $taillePage = 0;
        $tailleTab = 0;

        foreach($spectacles as $spectacle){
            if($taillePage > 5){
                $taillePage = 0;
                $tailleTab += 1;
            }
            $tabSpectacles[$tailleTab][$taillePage] = $spectacle;
            $taillePage += 1;
        }

        $parametres = $request->getQueryParams();
        $requestIndex = (int) ($parametres['page'] ?? 1);
        if($requestIndex > count($tabSpectacles)){
            throw new Exception('Index out of bounds');
        }
        
        $donnees = [
            'type' => 'collection',
            'count' => count($spectacles),
            'spectacles' => array_map(fn ($dto) => [
                'spectacle' => array_merge($dto->toArray(), [
                    'soiree_id' => $dto->soireeId,
                ]),
                'links' => [
                    'soiree' => $dto->soireeId === null
                        ? null
                        : ['href' => '/soirees/' . $dto->soireeId],
                ],
            ], $tabSpectacles[$requestIndex - 1]),
            'links' => [
                'self' => ['href' => '/spectacles'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
