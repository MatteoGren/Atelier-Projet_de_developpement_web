<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\dtos\PaiementDTO;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\PaiementInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\PlacesInsuffisantesException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;

class PayerCommandeAction
{
    private ServiceCommandeInterface $serviceCommande;

    public function __construct(ServiceCommandeInterface $serviceCommande)
    {
        $this->serviceCommande = $serviceCommande;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 400 : la requête n'a pas la forme attendue
        $corps = $request->getParsedBody();
        if (!is_array($corps)
            || !is_string($corps['numeroCarte'] ?? null)
            || !is_string($corps['dateExpiration'] ?? null)
            || !is_string($corps['code'] ?? null)) {
            throw new HttpBadRequestException($request, 'Corps invalide : numeroCarte, dateExpiration et code (texte) sont attendus.');
        }

        $idUtilisateur = UtilisateurConnecte::lire($request);

        $dto = new PaiementDTO($args['id'], $idUtilisateur, $corps['numeroCarte'], $corps['dateExpiration'], $corps['code']);

        try {
            $commande = $this->serviceCommande->payerCommande($dto);
        } catch (IdentifiantInvalideException | PaiementInvalideException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        } catch (CommandeIntrouvableException | SoireeIntrouvableException $e) {
            throw new HttpNotFoundException($request, $e->getMessage(), $e);
        } catch (CommandeNonPayableException | PlacesInsuffisantesException $e) {
            throw new HttpException($request, $e->getMessage(), 409, $e);
        }

        $donnees = [
            'type' => 'resource',
            'commande' => $commande->toArray(),
            'links' => [
                'self' => ['href' => '/commandes/' . $commande->id],
                'billets' => ['href' => '/commandes/' . $commande->id . '/billets'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
