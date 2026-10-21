<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\domain\entities\Commande;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class GetCommandeAction
{
    private ServiceCommandeInterface $serviceCommande;

    public function __construct(ServiceCommandeInterface $serviceCommande)
    {
        $this->serviceCommande = $serviceCommande;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $idUtilisateur = $request->getAttribute('idUtilisateur');

        if (!is_string($idUtilisateur) || $idUtilisateur === '') {
            $idUtilisateur = '11111111-0000-0000-0000-000000000001'; // fix temporaire (id deja en BDD) - sera modifié après quand on fera l'auth
        }

        try {
            $commande = $this->serviceCommande->afficherCommande($args['id'], $idUtilisateur);
        } catch (IdentifiantInvalideException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        } catch (CommandeIntrouvableException $e) {
            throw new HttpNotFoundException($request, $e->getMessage(), $e);
        }

        $liens = ['self' => ['href' => '/commandes/' . $commande->id]];
        // Le lien de paiement n'est proposé que tant que la commande attend d'être payée.
        if ($commande->statut === Commande::STATUT_EN_ATTENTE) {
            $liens['payer'] = ['href' => '/commandes/' . $commande->id . '/paiement'];
        }

        $donnees = [
            'type' => 'resource',
            'commande' => $commande->toArray(),
            'links' => $liens,
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
