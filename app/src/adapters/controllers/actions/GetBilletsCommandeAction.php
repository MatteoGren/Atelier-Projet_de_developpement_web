<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\ports\api\ServiceBilletInterface;
use nrv\domain\exceptions\CommandeNonPayeeException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;

class GetBilletsCommandeAction
{
    private ServiceBilletInterface $serviceBillet;

    public function __construct(ServiceBilletInterface $serviceBillet)
    {
        $this->serviceBillet = $serviceBillet;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $idUtilisateur = UtilisateurConnecte::lire($request);

        try {
            $billets = $this->serviceBillet->afficherBilletsCommande($args['id'], $idUtilisateur);
        } catch (IdentifiantInvalideException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        } catch (CommandeIntrouvableException | UtilisateurIntrouvableException | SoireeIntrouvableException $e) {
            throw new HttpNotFoundException($request, $e->getMessage(), $e);
        } catch (CommandeNonPayeeException $e) {
            throw new HttpException($request, $e->getMessage(), 409, $e);
        }

        $donnees = [
            'type' => 'collection',
            'count' => count($billets),
            'billets' => array_map(fn ($dto) => [
                'billet' => $dto->toArray(),
                'links' => [
                    'self' => ['href' => '/billets/' . $dto->token],
                    'soiree' => ['href' => '/soirees/' . $dto->idSoiree],
                ],
            ], $billets),
            'links' => [
                'self' => ['href' => '/commandes/' . $args['id'] . '/billets'],
                'commande' => ['href' => '/commandes/' . $args['id']],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
