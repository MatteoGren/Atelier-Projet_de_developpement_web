<?php

declare(strict_types=1);

namespace nrv\adapters\controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;

// Identifiant de l'utilisateur connecté : attribut de requête, sinon en-tête "Authorization: Bearer <id>".
class UtilisateurConnecte
{
    public static function lire(Request $request, string $message = 'Connexion requise.'): string
    {
        $idUtilisateur = $request->getAttribute('idUtilisateur');

        if (!$idUtilisateur) {
            $authHeader = $request->getHeaderLine('Authorization');
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $idUtilisateur = trim($matches[1]);
            }
        }

        if (!is_string($idUtilisateur) || trim($idUtilisateur) === '') {
            throw new HttpUnauthorizedException($request, $message);
        }

        return $idUtilisateur;
    }

    public static function exigerRole(
        Request $request,
        UtilisateurRepositoryInterface $utilisateurRepository,
        array $roles
    ): string {
        $idUtilisateur = self::lire($request);
        $utilisateur = $utilisateurRepository->findById($idUtilisateur);

        if ($utilisateur === null) {
            throw new HttpUnauthorizedException($request, 'Utilisateur inconnu.');
        }

        if (!in_array($utilisateur->getRole(), $roles, true)) {
            throw new HttpForbiddenException(
                $request,
                'Seuls les organisateurs et administrateurs peuvent effectuer cette action.'
            );
        }

        return $idUtilisateur;
    }
}
