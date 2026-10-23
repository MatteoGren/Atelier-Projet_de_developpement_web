<?php

declare(strict_types=1);

namespace nrv\adapters\auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

// Jeton de connexion signé (HS256) : seul le serveur, qui connaît le secret, peut en fabriquer un valide.
class GestionnaireJwt
{
    private const ALGORITHME = 'HS256';
    private const DUREE_SECONDES = 4 * 3600;

    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function creer(string $idUtilisateur, string $role): string
    {
        $maintenant = time();

        return JWT::encode([
            'sub' => $idUtilisateur,
            'role' => $role,
            'iat' => $maintenant,
            'exp' => $maintenant + self::DUREE_SECONDES,
        ], $this->secret, self::ALGORITHME);
    }

    // Identifiant contenu dans le jeton, ou null si le jeton est falsifié, expiré ou mal formé.
    public function lireIdUtilisateur(string $jeton): ?string
    {
        try {
            $contenu = JWT::decode($jeton, new Key($this->secret, self::ALGORITHME));
        } catch (Throwable) {
            return null;
        }

        return is_string($contenu->sub ?? null) ? $contenu->sub : null;
    }
}
