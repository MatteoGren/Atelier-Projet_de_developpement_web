<?php

declare(strict_types=1);

use nrv\domain\entities\Commande;
use nrv\tests\fakes\ApiEnMemoire;

// Parcours d'un client connecté : panier, paiement, billets, et ce qu'il ne doit pas pouvoir faire.

function unPanierHttp(array ...$lignes): array
{
    return ['lignes' => $lignes ?: [['idSoiree' => uneSoiree(1), 'nbNormal' => 2, 'nbReduit' => 1]]];
}

function uneCarte(string $numero = '4242424242424242'): array
{
    return ['numeroCarte' => $numero, 'dateExpiration' => '12/99', 'code' => '123'];
}

beforeEach(function () {
    $this->api = new ApiEnMemoire();
    // Commande en attente du client, créée par l'API.
    $this->commander = fn (?array $panier = null) => corps($this->api->post('/commandes', $panier ?? unPanierHttp(), CLIENT))['commande']['id'];
});

it('fait tout le parcours d\'achat : commande, paiement, billets, contrôle public du billet', function () {
    // 1. Validation du panier
    $creation = $this->api->post('/commandes', unPanierHttp(), CLIENT);
    $id = corps($creation)['commande']['id'];
    expect($creation->getStatusCode())->toBe(201)
        ->and($creation->getHeaderLine('Location'))->toBe('/commandes/' . $id)
        ->and(corps($creation)['commande'])->toMatchArray(['statut' => Commande::STATUT_EN_ATTENTE, 'montantTotal' => 65, 'nbBillets' => 3])
        ->and(corps($creation)['links']['payer'])->toBe(['href' => '/commandes/' . $id . '/paiement']);

    // 2. La commande en attente propose le paiement
    expect(corps($this->api->get('/commandes/' . $id, CLIENT))['links'])->toHaveKey('payer');

    // 3. Paiement
    $paiement = $this->api->post('/commandes/' . $id . '/paiement', uneCarte(), CLIENT);
    expect($paiement->getStatusCode())->toBe(200)
        ->and(corps($paiement)['commande']['statut'])->toBe(Commande::STATUT_VALIDEE)
        ->and(corps($paiement)['links']['billets'])->toBe(['href' => '/commandes/' . $id . '/billets']);

    // 4. Une commande payée ne propose plus le paiement
    expect(corps($this->api->get('/commandes/' . $id, CLIENT))['links'])->not->toHaveKey('payer');

    // 5. Billets de la commande, au nom de l'acheteur
    $billets = corps($this->api->get('/commandes/' . $id . '/billets', CLIENT));
    expect($billets['count'])->toBe(3)
        ->and($billets['billets'][0]['billet']['acheteur'])->toContain('Martin')
        ->and($billets['billets'][0]['links']['soiree'])->toBe(['href' => '/soirees/' . uneSoiree(1)]);

    // 6. Le lien du billet s'ouvre sans connexion (contrôle à l'entrée)
    $public = $this->api->get($billets['billets'][0]['links']['self']['href']);
    expect($public->getStatusCode())->toBe(200)
        ->and(corps($public)['billet']['ref'])->toBe($billets['billets'][0]['billet']['ref']);

    // 7. « Mes billets »
    expect(corps($this->api->get('/billets', CLIENT))['count'])->toBe(3);
});

it('n\'affiche aucun billet tant que la commande n\'est pas payée', function () {
    $id = ($this->commander)();
    $token = $this->api->commandes->findById($id)->getBillets()[0]->getToken();

    expect($this->api->get('/commandes/' . $id . '/billets', CLIENT)->getStatusCode())->toBe(409)
        ->and($this->api->get('/billets/' . $token)->getStatusCode())->toBe(404)
        ->and(corps($this->api->get('/billets', CLIENT))['count'])->toBe(0);
});

it('refuse de payer deux fois la même commande', function () {
    $id = ($this->commander)();
    $this->api->post('/commandes/' . $id . '/paiement', uneCarte(), CLIENT);

    expect($this->api->post('/commandes/' . $id . '/paiement', uneCarte(), CLIENT)->getStatusCode())->toBe(409);
});

it('refuse une carte invalide et laisse la commande en attente', function (array $carte) {
    $id = ($this->commander)();

    expect($this->api->post('/commandes/' . $id . '/paiement', $carte, CLIENT)->getStatusCode())->toBe(400)
        ->and($this->api->commandes->findById($id)->estPayee())->toBeFalse();
})->with([
    'numéro trop court' => [uneCarte('4242')],
    'carte expirée' => [['numeroCarte' => '4242424242424242', 'dateExpiration' => '01/20', 'code' => '123']],
    'champ manquant' => [['numeroCarte' => '4242424242424242']],
]);

it('refuse une commande sur une soirée complète', function () {
    $reponse = $this->api->post('/commandes', unPanierHttp(['idSoiree' => uneSoiree(3), 'nbNormal' => 1, 'nbReduit' => 0]), CLIENT);

    expect($reponse->getStatusCode())->toBe(409)
        ->and($this->api->commandes->commandes)->toBeEmpty();
});

it('refuse un panier invalide sans rien enregistrer', function (mixed $corps) {
    expect($this->api->post('/commandes', $corps, CLIENT)->getStatusCode())->toBe(400)
        ->and($this->api->commandes->commandes)->toBeEmpty();
})->with([
    'sans lignes' => [['autre' => 1]],
    'panier vide' => [['lignes' => []]],
    'quantité en texte' => [['lignes' => [['idSoiree' => uneSoiree(1), 'nbNormal' => '2', 'nbReduit' => 0]]]],
    'quantité négative' => [['lignes' => [['idSoiree' => uneSoiree(1), 'nbNormal' => -1, 'nbReduit' => 2]]]],
    'soirée inexistante' => [['lignes' => [['idSoiree' => uneSoiree(99), 'nbNormal' => 1, 'nbReduit' => 0]]]],
]);

it('cache à un autre client la commande et les billets du client, comme s\'ils n\'existaient pas', function () {
    $id = ($this->commander)();

    expect($this->api->get('/commandes/' . $id, AUTRE_CLIENT)->getStatusCode())->toBe(404)
        ->and($this->api->post('/commandes/' . $id . '/paiement', uneCarte(), AUTRE_CLIENT)->getStatusCode())->toBe(404)
        ->and($this->api->commandes->findById($id)->estPayee())->toBeFalse();

    $this->api->post('/commandes/' . $id . '/paiement', uneCarte(), CLIENT);

    expect($this->api->get('/commandes/' . $id . '/billets', AUTRE_CLIENT)->getStatusCode())->toBe(404)
        ->and(corps($this->api->get('/billets', AUTRE_CLIENT))['count'])->toBe(0);
});

it('interdit au client le back-office, sans rien créer', function (string $methode, string $uri, ?array $corps) {
    $reponse = $this->api->envoyer($methode, $uri, $corps, CLIENT);

    expect($reponse->getStatusCode())->toBe(403)
        ->and($this->api->soirees->findAll())->toHaveCount(3)
        ->and($this->api->spectacles->findAll())->toHaveCount(4);
})->with([
    'tableau de bord' => ['GET', '/soirees', null],
    'créer une soirée' => ['POST', '/soirees', ['nom' => 'X', 'thematique' => 'X', 'date' => '2027-07-01 20:00', 'lieu' => 'X', 'capacite' => 10, 'spectacles' => ['libre-1', 'libre-2']]],
    'créer un spectacle' => ['POST', '/spectacles', ['titre' => 'X', 'artistes' => 'X', 'style' => 'rock', 'horaire' => '2027-07-01 20:00']],
]);
