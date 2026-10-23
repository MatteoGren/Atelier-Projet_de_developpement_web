<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceSoireeInterface;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListerSoireeBackOfficeAction{

    private ServiceSoireeInterface $serviceSoiree;

     public function __construct(ServiceSoireeInterface $serviceSoiree)
    {
        $this->serviceSoiree = $serviceSoiree;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $soirees = $this->serviceSoiree->listerSoireeBackOffice();

        $donnees = [
            'type' => 'collection',
            'count' => count($soirees),
            'soirees' => array_map(fn ($dto) => [
                'soiree' => $dto->toArray(),
                'links' => [
                    'self' => ['href' => '/soirees/' . $dto->id],
                ],
            ], $soirees),
            'links' => [
                'self' => ['href' => '/soirees'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
