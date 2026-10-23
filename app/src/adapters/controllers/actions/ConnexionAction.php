<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use nrv\application\exceptions\MotDePasseInvalideException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

final class ConnexionAction
{
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(ServiceUtilisateurInterface $serviceUtilisateur)
    {
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data) || !is_string($data['email'] ?? null) || !is_string($data['motDePasse'] ?? null)) {
            throw new HttpBadRequestException($request, 'Corps invalide : email et motDePasse (texte) sont attendus.');
        }

        try {
            $utilisateur = $this->serviceUtilisateur->connecter($data['email'], $data['motDePasse']);
        } catch (UtilisateurIntrouvableException | MotDePasseInvalideException $e) {
            // Même message dans les deux cas : on ne révèle pas si l'email a un compte.
            throw new HttpUnauthorizedException($request, 'Email ou mot de passe incorrect.', $e);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'     => $utilisateur->getIdUtilisateur(),
            'email'  => $utilisateur->getEmail(),
            'role'   => $utilisateur->getRole(),
            'nom'    => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
        ];

        $donnees = [
            'type' => 'resource',
            'utilisateur' => $_SESSION['user'],
            'links' => [
                'billets' => ['href' => '/billets'],
                'spectacles' => ['href' => '/spectacles'],
            ],
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}
