<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\middlewares;

use nrv\adapters\auth\GestionnaireJwt;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

// Décode le jeton "Authorization: Bearer <jwt>" et range l'identifiant dans l'attribut idUtilisateur.
// Ne refuse rien lui-même : chaque action protégée vérifie la présence de l'attribut (UtilisateurConnecte).
class LectureJeton implements MiddlewareInterface
{
    private GestionnaireJwt $gestionnaireJwt;

    public function __construct(GestionnaireJwt $gestionnaireJwt)
    {
        $this->gestionnaireJwt = $gestionnaireJwt;
    }

    public function process(Request $request, Handler $handler): Response
    {
        if (preg_match('/^Bearer\s+(\S+)$/i', $request->getHeaderLine('Authorization'), $correspondances)) {
            $idUtilisateur = $this->gestionnaireJwt->lireIdUtilisateur($correspondances[1]);
            if ($idUtilisateur !== null) {
                $request = $request->withAttribute('idUtilisateur', $idUtilisateur);
            }
        }

        return $handler->handle($request);
    }
}
