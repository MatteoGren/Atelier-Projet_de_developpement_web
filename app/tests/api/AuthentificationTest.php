<?php

declare(strict_types=1);

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
})->with(['Bearer ', 'Bearer    ', 'Basic dXNlcjpwYXNz', CLIENT]);

it('répond 401 au back-office pour un compte qui n\'existe pas', function (string $methode, string $uri, ?array $corps) {
    expect($this->api->envoyer($methode, $uri, $corps, '99999999-0000-0000-0000-000000000000')->getStatusCode())->toBe(401);
})->with([
    'tableau de bord' => ['GET', '/soirees', null],
    'créer une soirée' => ['POST', '/soirees', ['nom' => 'X']],
    'créer un spectacle' => ['POST', '/spectacles', ['titre' => 'X']],
]);
