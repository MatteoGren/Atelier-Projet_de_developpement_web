<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use nrv\tests\fakes\ApiEnMemoire;

// Routes protégées : sans connexion valide, rien n'est lu ni modifié.

beforeEach(function () {
    $this->api = new ApiEnMemoire();
});

dataset('routes protégées', [
    'tableau de bord' => ['GET', '/soirees', null],
    'créer une soirée' => ['POST', '/soirees', ['nom' => 'X']],
    'créer un spectacle' => ['POST', '/spectacles', ['titre' => 'X']],
    'valider un panier' => ['POST', '/commandes', ['lignes' => [['idSoiree' => '22222222-0000-0000-0000-000000000001', 'nbNormal' => 1, 'nbReduit' => 0]]]],
    'voir une commande' => ['GET', '/commandes/55555555-0000-0000-0000-000000000001', null],
    'payer une commande' => ['POST', '/commandes/55555555-0000-0000-0000-000000000001/paiement', ['numeroCarte' => '4242424242424242', 'dateExpiration' => '12/99', 'code' => '123']],
    'billets d\'une commande' => ['GET', '/commandes/55555555-0000-0000-0000-000000000001/billets', null],
    'mes billets' => ['GET', '/billets', null],
]);

it('répond 401 sans en-tête de connexion', function (string $methode, string $uri, ?array $corps) {
    expect($this->api->envoyer($methode, $uri, $corps)->getStatusCode())->toBe(401)
        ->and($this->api->commandes->commandes)->toBeEmpty();
})->with('routes protégées');

it('répond 401 avec un jeton vide ou un en-tête mal formé', function (string $entete) {
    $reponse = $this->api->envoyer('GET', '/billets', entetes: ['Authorization' => $entete]);

    expect($reponse->getStatusCode())->toBe(401);
})->with(['Bearer ', 'Bearer    ', 'Basic dXNlcjpwYXNz', CLIENT, 'Bearer ' . CLIENT]);

it('répond 401 avec un jeton signé par un autre secret ou expiré', function (string $jeton) {
    $reponse = $this->api->envoyer('GET', '/billets', entetes: ['Authorization' => 'Bearer ' . $jeton]);

    expect($reponse->getStatusCode())->toBe(401);
})->with([
    'autre secret' => fn () => JWT::encode(['sub' => CLIENT, 'exp' => time() + 3600], str_repeat('x', 48), 'HS256'),
    'expiré' => fn () => JWT::encode(['sub' => CLIENT, 'exp' => time() - 60], ApiEnMemoire::SECRET_JWT, 'HS256'),
    'algorithme none' => fn () => rtrim(strtr(base64_encode('{"alg":"none","typ":"JWT"}'), '+/', '-_'), '=') . '.'
        . rtrim(strtr(base64_encode('{"sub":"' . CLIENT . '"}'), '+/', '-_'), '=') . '.',
]);

it('ne renvoie aucun détail technique sur une erreur interne', function () {
    $this->api->soirees->enPanne = true;
    $reponse = $this->api->get('/soirees/' . uneSoiree(1));

    expect($reponse->getStatusCode())->toBe(500)
        ->and(corps($reponse))->toBe(['message' => 'Erreur interne du serveur.']);
});

it('répond 401 au back-office pour un compte qui n\'existe pas', function (string $methode, string $uri, ?array $corps) {
    expect($this->api->envoyer($methode, $uri, $corps, '99999999-0000-0000-0000-000000000000')->getStatusCode())->toBe(401);
})->with([
    'tableau de bord' => ['GET', '/soirees', null],
    'créer une soirée' => ['POST', '/soirees', ['nom' => 'X']],
    'créer un spectacle' => ['POST', '/spectacles', ['titre' => 'X']],
]);
