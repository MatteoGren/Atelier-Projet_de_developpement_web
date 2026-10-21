<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\ports\api\ServiceBilletInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

// Accessible sans connexion : le token aléatoire de l'URL sert de clé d'accès au billet.
class GetBilletAction
{
    private ServiceBilletInterface $serviceBillet;

    public function __construct(ServiceBilletInterface $serviceBillet)
    {
        $this->serviceBillet = $serviceBillet;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $billet = $this->serviceBillet->afficherBillet($args['token']);
        } catch (BilletIntrouvableException | UtilisateurIntrouvableException | SoireeIntrouvableException $e) {
            throw new HttpNotFoundException($request, $e->getMessage(), $e);
        }

        $donnees = [
            'type' => 'resource',
            'billet' => $billet->toArray(),
            'links' => [
                'self' => ['href' => '/billets/' . $billet->token],
                'soiree' => ['href' => '/soirees/' . $billet->idSoiree],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
