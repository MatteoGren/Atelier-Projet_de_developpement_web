<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListerSoireeBackOfficeAction{

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

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Ventes et recettes : réservées au back-office.
        UtilisateurConnecte::exigerRole(
            $request,
            $this->utilisateurRepository,
            ['organisateur', 'administrateur']
        );

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
