<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use Exception;

final class ConnexionAction
{
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(ServiceUtilisateurInterface $serviceUtilisateur)
    {
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];
        $email = $data['email'] ?? '';
        $motDePasse = $data['motDePasse'] ?? '';

        try {
            $utilisateur = $this->serviceUtilisateur->connecter($email, $motDePasse);  // Appel à la méthode connecter du service utilisateur

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            session_regenerate_id(true);

            // Stockage des informations de l'utilisateur dans la session
            $_SESSION['user'] = [
                'id' => $utilisateur->getIdUtilisateur(),
                'email' => $utilisateur->getEmail(),
                'role' => $utilisateur->getRole()
            ];

            // renvoyer vers la page d'accueil après une connexion réussie
            return $response
                ->withHeader('Location', '/')
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