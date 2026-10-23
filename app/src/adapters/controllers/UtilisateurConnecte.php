<?php

declare(strict_types=1);

namespace nrv\adapters\controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;

// Identifiant de l'utilisateur connecté, posé par le middleware LectureJeton à partir d'un JWT valide.
class UtilisateurConnecte
{
    public static function lire(Request $request, string $message = 'Connexion requise.'): string
    {
        $idUtilisateur = $request->getAttribute('idUtilisateur');

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
