<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use Exception;

final class RegisterAction
{
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(ServiceUtilisateurInterface $serviceUtilisateur)
    {
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];
        
        $nom = $data['nom'] ?? '';
        $prenom = $data['prenom'] ?? '';
        $email = $data['email'] ?? '';
        $telephone = $data['telephone'] ?? '';
        $motDePasse = $data['motDePasse'] ?? '';
        $role = $data['role'] ?? 'client';

        try {
            $this->serviceUtilisateur->register($nom, $prenom, $email, $telephone, $motDePasse, $role);

            return $response
                ->withHeader('Location', '/connexion')
                ->withStatus(302);

        } catch (Exception $e) {
            $payload = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($payload);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}