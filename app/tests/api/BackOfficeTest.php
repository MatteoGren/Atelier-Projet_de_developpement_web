<?php

declare(strict_types=1);

use nrv\tests\fakes\ApiEnMemoire;

// Back-office : organisateurs et administrateurs créent la programmation et suivent les ventes.

function uneSoireeHttp(array $champs = []): array
{
    return array_merge([
        'nom' => 'Nuit Métal', 'thematique' => 'Metal', 'date' => '2027-06-20 20:00',
        'lieu' => 'Zénith', 'capacite' => 300, 'spectacles' => ['libre-1', 'libre-2'],
    ], $champs);
}

function unSpectacleHttp(array $champs = []): array
{
    return array_merge([
        'titre' => 'Nouveau', 'artistes' => 'Groupe', 'style' => 'rock', 'horaire' => '2027-06-21 21:00',
    ], $champs);
}

beforeEach(function () {
    $this->api = new ApiEnMemoire();
});

it('montre le tableau de bord des ventes', function (string $utilisateur) {
    $reponse = $this->api->get('/soirees', $utilisateur);
    $donnees = corps($reponse);

    expect($reponse->getStatusCode())->toBe(200)
        ->and($donnees['type'])->toBe('collection')
        ->and($donnees['count'])->toBe(3)
        ->and($donnees['soirees'][0]['soiree'])->toBe([
            'id' => uneSoiree(1), 'nom' => 'Ouverture Électrique', 'placesVendues' => 3, 'capacite' => 800, 'recette' => 65,
        ])
        ->and($donnees['soirees'][0]['links']['self'])->toBe(['href' => '/soirees/' . uneSoiree(1)])
        ->and($donnees['links']['self'])->toBe(['href' => '/soirees']);
})->with(['organisateur' => ORGANISATEUR, 'administrateur' => ADMINISTRATEUR]);

it('crée une soirée qui apparaît au programme avec ses spectacles et au tableau de bord', function (string $utilisateur) {
    $reponse = $this->api->post('/soirees', uneSoireeHttp(), $utilisateur);
    $id = corps($reponse)['soiree']['id'];

    expect($reponse->getStatusCode())->toBe(201)
        ->and($reponse->getHeaderLine('Location'))->toBe('/soirees/' . $id)
        ->and(corps($reponse)['links']['collection'])->toBe(['href' => '/soirees']);

    $detail = corps($this->api->get('/soirees/' . $id));
    expect(array_column($detail['soiree']['spectacles'], 'id'))->toBe(['libre-1', 'libre-2'])
        ->and($detail['soiree']['tarifs'])->toBe(['normal' => 25, 'reduit' => 15]);

    $tableau = corps($this->api->get('/soirees', $utilisateur));
    expect($tableau['count'])->toBe(4)
        ->and(end($tableau['soirees'])['soiree'])->toMatchArray(['nom' => 'Nuit Métal', 'placesVendues' => 0, 'capacite' => 300]);
})->with(['organisateur' => ORGANISATEUR, 'administrateur' => ADMINISTRATEUR]);

it('refuse une soirée invalide sans rien créer', function (mixed $corps) {
    expect($this->api->post('/soirees', $corps, ORGANISATEUR)->getStatusCode())->toBe(400)
        ->and($this->api->soirees->findAll())->toHaveCount(3);
})->with([
    'capacité nulle' => [uneSoireeHttp(['capacite' => 0])],
    'un seul spectacle' => [uneSoireeHttp(['spectacles' => ['libre-1']])],
    'date invalide' => [uneSoireeHttp(['date' => 'demain soir'])],
    'corps qui n\'est pas un objet' => ['"texte"'],
]);

it('crée un spectacle hors soirée, visible dans le programme', function (string $utilisateur) {
    $reponse = $this->api->post('/spectacles', unSpectacleHttp(['images' => 'img/a.jpg; img/b.jpg']), $utilisateur);
    $spectacle = corps($reponse)['spectacle'];

    expect($reponse->getStatusCode())->toBe(201)
        ->and($spectacle['soiree_id'])->toBeNull()
        ->and($spectacle['images'])->toBe(['img/a.jpg', 'img/b.jpg'])
        ->and(corps($reponse)['links'])->toBe(['collection' => ['href' => '/spectacles']])
        ->and(corps($this->api->get('/spectacles?style=rock'))['count'])->toBe(2);
})->with(['organisateur' => ORGANISATEUR, 'administrateur' => ADMINISTRATEUR]);

it('crée un spectacle rattaché à une soirée avec un lien vers elle', function () {
    $reponse = $this->api->post('/spectacles', unSpectacleHttp(['soiree_id' => uneSoiree(2)]), ORGANISATEUR);

    expect(corps($reponse)['links']['soiree'])->toBe(['href' => '/soirees/' . uneSoiree(2)])
        ->and(corps($this->api->get('/soirees/' . uneSoiree(2)))['soiree']['spectacles'])->toHaveCount(2);
});

it('refuse un spectacle invalide sans rien créer', function (mixed $corps) {
    expect($this->api->post('/spectacles', $corps, ORGANISATEUR)->getStatusCode())->toBe(400)
        ->and($this->api->spectacles->findAll())->toHaveCount(4);
})->with([
    'sans titre' => [unSpectacleHttp(['titre' => ''])],
    'titre en blancs' => [unSpectacleHttp(['titre' => '   '])],
    'sans horaire' => [unSpectacleHttp(['horaire' => null])],
    'horaire invalide' => [unSpectacleHttp(['horaire' => 'ce soir tard'])],
    'corps qui n\'est pas un objet' => ['"texte"'],
]);
