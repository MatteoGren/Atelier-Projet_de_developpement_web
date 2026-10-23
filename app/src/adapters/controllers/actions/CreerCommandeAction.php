<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\adapters\controllers\UtilisateurConnecte;
use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\dtos\LigneCommandeDTO;
use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\domain\exceptions\PlacesInsuffisantesException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;

class CreerCommandeAction
{
    private ServiceCommandeInterface $serviceCommande;

    public function __construct(ServiceCommandeInterface $serviceCommande)
    {
        $this->serviceCommande = $serviceCommande;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $corps = $request->getParsedBody();
        if (!is_array($corps) || !isset($corps['lignes']) || !is_array($corps['lignes'])) {
            throw new HttpBadRequestException($request, 'Corps invalide : un tableau "lignes" est attendu.');
        }

        $lignes = [];
        foreach ($corps['lignes'] as $ligne) {
            if (!is_array($ligne)
                || !is_string($ligne['idSoiree'] ?? null)
                || !is_int($ligne['nbNormal'] ?? null)
                || !is_int($ligne['nbReduit'] ?? null)) {
                throw new HttpBadRequestException($request, 'Ligne invalide : idSoiree (texte), nbNormal et nbReduit (entiers) sont attendus.');
            }
            $lignes[] = new LigneCommandeDTO($ligne['idSoiree'], $ligne['nbNormal'], $ligne['nbReduit']);
        }

        $idUtilisateur = UtilisateurConnecte::lire($request, 'Connexion requise pour valider le panier.');

        $dto = new CreerCommandeDTO($idUtilisateur, $lignes);

        try {
            $commande = $this->serviceCommande->creerCommande($dto);
        } catch (CommandeInvalideException | IdentifiantInvalideException | SoireeIntrouvableException $e) {
            throw new HttpBadRequestException($request, $e->getMessage());
        } catch (PlacesInsuffisantesException $e) {
            throw new HttpException($request, $e->getMessage(), 409, $e);
        }

        $donnees = [
            'type' => 'resource',
            'commande' => $commande->toArray(),
            'links' => [
                'self' => ['href' => '/commandes/' . $commande->id],
                'payer' => ['href' => '/commandes/' . $commande->id . '/paiement'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Location', '/commandes/' . $commande->id)
            ->withStatus(201);
    }
}
