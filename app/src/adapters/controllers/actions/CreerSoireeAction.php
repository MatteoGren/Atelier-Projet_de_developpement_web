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
use Slim\Exception\HttpBadRequestException;

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

        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new HttpBadRequestException($request, 'Corps invalide : un objet JSON est attendu.');
        }

        // Les tarifs sont fixes par la règle métier de Soiree.
        $dto = new CreerSoireeDTO(
            nom: (string) ($body['nom'] ?? ''),
            thematique: (string) ($body['thematique'] ?? ''),
            date: (string) ($body['date'] ?? ''),
            lieu: (string) ($body['lieu'] ?? ''),
            capacite: (int) ($body['capacite'] ?? 0),
            spectacles: (array) ($body['spectacles'] ?? [])
        );

        try {
            $id = $this->serviceSoiree->creerSoiree($dto);
        } catch (InvalidArgumentException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $soiree = $this->serviceSoiree->afficherSoiree($id);

        $donnees = [
            'type' => 'resource',
            'soiree' => $soiree->toArray(),
            'links' => [
                'self' => ['href' => '/soirees/' . $id],
                'collection' => ['href' => '/soirees'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Location', '/soirees/' . $id)
            ->withStatus(201);
    }
}
