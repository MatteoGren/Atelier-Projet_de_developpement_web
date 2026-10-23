<?php

declare(strict_types=1);

use nrv\tests\fakes\ApiEnMemoire;

// Parcours d'un visiteur non connecté, de bout en bout à travers Slim.

beforeEach(function () {
    $this->api = new ApiEnMemoire();
});

it('présente les points d\'entrée de l\'API depuis l\'accueil', function () {
    $reponse = $this->api->get('/');

    expect($reponse->getStatusCode())->toBe(200)
        ->and($reponse->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and(corps($reponse)['links'])->toMatchArray([
            'spectacles' => ['href' => '/spectacles'],
            'inscription' => ['href' => '/utilisateurs'],
            'connexion' => ['href' => '/sessions'],
        ]);
});

it('liste le programme avec un lien vers la soirée de chaque spectacle', function () {
    $donnees = corps($this->api->get('/spectacles'));

    expect($donnees['type'])->toBe('collection')
        ->and($donnees['count'])->toBe(4)
        ->and($donnees['spectacles'][0]['spectacle']['titre'])->toBe('Highway Night')
        ->and($donnees['spectacles'][0]['links']['soiree'])->toBe(['href' => '/soirees/' . uneSoiree(1)])
        // Un spectacle pas encore programmé n'a pas de soirée.
        ->and($donnees['spectacles'][2]['links']['soiree'])->toBeNull();
});

it('filtre le programme par date ou par style', function (string $requete, array $titres) {
    $donnees = corps($this->api->get('/spectacles?' . $requete));

    expect(array_map(fn ($e) => $e['spectacle']['titre'], $donnees['spectacles']))->toBe($titres)
        ->and($donnees['links']['self']['href'])->toBe('/spectacles?' . $requete);
})->with([
    'style' => ['style=blues', ['Blues de la rivière']],
    'date' => ['date=2027-06-20', ['Libre 1', 'Libre 2']],
    'date et style' => ['date=2027-06-04&style=rock', ['Highway Night']],
    'aucun résultat' => ['style=jazz', []],
]);

it('découpe le programme en pages de 6 spectacles', function () {
    $donnees = corps($this->api->get('/spectacles?page=1'));

    expect($donnees['count'])->toBe(4)
        ->and($donnees['page'])->toBe(1)
        ->and($donnees['pages'])->toBe(1)
        ->and($donnees['spectacles'])->toHaveCount(4)
        ->and($donnees['links']['self'])->toBe(['href' => '/spectacles?page=1'])
        ->and($donnees['links'])->not->toHaveKeys(['prev', 'next']);
});

it('répond 404 pour une page après la dernière et 400 pour un numéro invalide', function () {
    expect($this->api->get('/spectacles?page=2')->getStatusCode())->toBe(404);
    foreach (['0', '-1', 'abc', '1.5'] as $page) {
        expect($this->api->get('/spectacles?page=' . $page)->getStatusCode())->toBe(400);
    }
});

it('refuse une date de filtre mal formée', function (string $date) {
    expect($this->api->get('/spectacles?date=' . $date)->getStatusCode())->toBe(400);
})->with(['pas-une-date', '2027-02-30', '04/06/2027']);

it('affiche le détail d\'une soirée avec ses tarifs et ses spectacles', function () {
    $reponse = $this->api->get('/soirees/' . uneSoiree(1));
    $donnees = corps($reponse);

    expect($reponse->getStatusCode())->toBe(200)
        ->and($donnees['soiree']['nom'])->toBe('Ouverture Électrique')
        ->and($donnees['soiree']['tarifs'])->toBe(['normal' => 25, 'reduit' => 15])
        ->and(array_column($donnees['soiree']['spectacles'], 'titre'))->toBe(['Highway Night'])
        ->and($donnees['links']['self'])->toBe(['href' => '/soirees/' . uneSoiree(1)]);
});

it('répond 404 pour une soirée inexistante et 400 pour un identifiant invalide', function () {
    expect($this->api->get('/soirees/' . uneSoiree(99))->getStatusCode())->toBe(404)
        ->and($this->api->get('/soirees/pas-un-uuid')->getStatusCode())->toBe(400);
});

it('inscrit un visiteur, toujours comme client même s\'il demande un autre rôle', function () {
    $reponse = $this->api->post('/utilisateurs', [
        'nom' => 'Petit', 'prenom' => 'Jo', 'email' => 'jo@example.com',
        'telephone' => '0644444444', 'motDePasse' => 'motdepasse',
        'role' => 'administrateur',
    ]);

    $jo = $this->api->utilisateurs->findByEmail('jo@example.com');
    expect($reponse->getStatusCode())->toBe(201)
        ->and(corps($reponse)['links']['connexion'])->toBe(['href' => '/sessions'])
        ->and(corps($reponse)['utilisateur'])->not->toHaveKey('motDePasse')
        ->and($jo->getRole())->toBe('client');
});

it('refuse une inscription avec un email déjà utilisé', function () {
    $reponse = $this->api->post('/utilisateurs', [
        'nom' => 'X', 'prenom' => 'Y', 'email' => 'lea@example.com', 'telephone' => '', 'motDePasse' => 'x',
    ]);

    expect($reponse->getStatusCode())->toBe(409)
        ->and($this->api->utilisateurs->utilisateurs)->toHaveCount(4);
});

it('refuse une inscription incomplète', function () {
    $reponse = $this->api->post('/utilisateurs', ['email' => 'jo@example.com', 'motDePasse' => 'x']);

    expect($reponse->getStatusCode())->toBe(400)
        ->and($this->api->utilisateurs->findByEmail('jo@example.com'))->toBeNull();
});

it('connecte un utilisateur et renvoie son identifiant et son rôle, sans le mot de passe', function () {
    $reponse = $this->api->post('/sessions', ['email' => 'orga@example.com', 'motDePasse' => 'secret123']);
    $utilisateur = corps($reponse)['utilisateur'];

    expect($reponse->getStatusCode())->toBe(201)
        ->and($utilisateur['id'])->toBe(ORGANISATEUR)
        ->and($utilisateur['role'])->toBe('organisateur')
        ->and($utilisateur)->not->toHaveKey('motDePasse');
});

it('refuse la connexion avec le même message, que l\'email existe ou non', function () {
    $mauvaisMotDePasse = $this->api->post('/sessions', ['email' => 'lea@example.com', 'motDePasse' => 'faux']);
    $emailInconnu = $this->api->post('/sessions', ['email' => 'inconnu@example.com', 'motDePasse' => 'secret123']);

    expect($mauvaisMotDePasse->getStatusCode())->toBe(401)
        ->and($emailInconnu->getStatusCode())->toBe(401)
        ->and(corps($mauvaisMotDePasse))->toBe(corps($emailInconnu));
});

it('répond 404 pour un billet inconnu ou un token mal formé', function (string $token) {
    expect($this->api->get('/billets/' . $token)->getStatusCode())->toBe(404);
})->with([
    'token inconnu' => str_repeat('a', 64),
    'token mal formé' => 'pas-un-token',
]);

it('renvoie les erreurs en JSON avec les en-têtes CORS pour le front autorisé', function () {
    $reponse = $this->api->envoyer('GET', '/soirees/' . uneSoiree(99), entetes: ['Origin' => ApiEnMemoire::ORIGINE_FRONT]);

    expect($reponse->getStatusCode())->toBe(404)
        ->and(corps($reponse))->toHaveKey('message')
        ->and($reponse->getHeaderLine('Access-Control-Allow-Origin'))->toBe(ApiEnMemoire::ORIGINE_FRONT)
        ->and($reponse->getHeaderLine('Access-Control-Allow-Headers'))->toContain('Authorization');
});

it('n\'autorise pas une origine absente de la liste CORS', function () {
    $reponse = $this->api->envoyer('GET', '/spectacles', entetes: ['Origin' => 'http://site-pirate.example']);

    expect($reponse->getStatusCode())->toBe(200)
        ->and($reponse->hasHeader('Access-Control-Allow-Origin'))->toBeFalse();
});

it('renvoie à la connexion un jeton JWT accepté par les routes protégées', function () {
    $jeton = corps($this->api->post('/sessions', ['email' => 'lea@example.com', 'motDePasse' => 'secret123']))['token'];
    $reponse = $this->api->envoyer('GET', '/billets', entetes: ['Authorization' => 'Bearer ' . $jeton]);

    expect($jeton)->toBeString()->toContain('.')
        ->and($reponse->getStatusCode())->toBe(200);
});
