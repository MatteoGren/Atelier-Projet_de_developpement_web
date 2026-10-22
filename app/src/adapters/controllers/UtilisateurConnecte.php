<?php

declare(strict_types=1);

namespace nrv\adapters\controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpUnauthorizedException;

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
}
