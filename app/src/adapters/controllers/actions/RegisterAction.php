<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;

final class RegisterAction
{
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(ServiceUtilisateurInterface $serviceUtilisateur)
    {
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new HttpBadRequestException($request, 'Corps invalide : un objet JSON est attendu.');
        }

        foreach (['nom', 'prenom', 'email', 'telephone', 'motDePasse'] as $champ) {
            if (!is_string($data[$champ] ?? null)) {
                throw new HttpBadRequestException($request, 'Corps invalide : nom, prenom, email, telephone et motDePasse (texte) sont attendus.');
            }
        }

        try {
            $this->serviceUtilisateur->register($data['nom'], $data['prenom'], $data['email'], $data['telephone'], $data['motDePasse'], 'client');
        } catch (InvalidArgumentException $e) {
            // Seul cas levé par le service : un compte existe déjà avec cet email.
            throw new HttpException($request, $e->getMessage(), 409, $e);
        }

        $donnees = [
            'type' => 'resource',
            'utilisateur' => [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
            ],
            'links' => [
                'connexion' => ['href' => '/sessions'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}
