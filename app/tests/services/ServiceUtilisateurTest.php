<?php

declare(strict_types=1);

use nrv\application\exceptions\MotDePasseInvalideException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\usecases\ServiceUtilisateur;
use nrv\domain\entities\Utilisateur;
use nrv\tests\fakes\FakeUtilisateurRepository;

beforeEach(function () {
    // Un compte existant : lea@example.com / secret123
    $this->utilisateurs = new FakeUtilisateurRepository([
        new Utilisateur(UTILISATEUR_TEST, 'Martin', 'Léa', 'lea@example.com', '0600000000', password_hash('secret123', PASSWORD_DEFAULT), 'client'),
    ]);
    $this->service = new ServiceUtilisateur($this->utilisateurs);
});

it('inscrit un nouvel utilisateur avec le rôle demandé', function () {
    $this->service->register('Durand', 'Paul', 'paul@example.com', '0611111111', 'motdepasse', 'client');

    $paul = $this->utilisateurs->findByEmail('paul@example.com');
    expect($paul)->not->toBeNull()
        ->and($paul->getNom())->toBe('Durand')
        ->and($paul->getPrenom())->toBe('Paul')
        ->and($paul->getTelephone())->toBe('0611111111')
        ->and($paul->getRole())->toBe('client')
        ->and($paul->getIdUtilisateur())->not->toBeEmpty();
});

it('enregistre le mot de passe haché, jamais en clair', function () {
    $this->service->register('Durand', 'Paul', 'paul@example.com', '0611111111', 'motdepasse', 'client');

    $hash = $this->utilisateurs->findByEmail('paul@example.com')->getMotDePasse();
    expect($hash)->not->toBe('motdepasse')
        ->and(password_verify('motdepasse', $hash))->toBeTrue();
});

it('refuse une inscription avec un email déjà utilisé', function () {
    expect(fn () => $this->service->register('Autre', 'Léa', 'lea@example.com', '', 'x', 'client'))
        ->toThrow(InvalidArgumentException::class);

    expect($this->utilisateurs->utilisateurs)->toHaveCount(1);
});

it('connecte un utilisateur avec le bon mot de passe', function () {
    $utilisateur = $this->service->connecter('lea@example.com', 'secret123');

    expect($utilisateur->getIdUtilisateur())->toBe(UTILISATEUR_TEST);
});

it('refuse la connexion avec un mauvais mot de passe', function () {
    $this->service->connecter('lea@example.com', 'mauvais');
})->throws(MotDePasseInvalideException::class);

it('refuse la connexion avec un email inconnu', function () {
    $this->service->connecter('inconnu@example.com', 'secret123');
})->throws(UtilisateurIntrouvableException::class);

it('permet de se connecter juste après l\'inscription', function () {
    $this->service->register('Durand', 'Paul', 'paul@example.com', '0611111111', 'motdepasse', 'client');

    expect($this->service->connecter('paul@example.com', 'motdepasse')->getEmail())->toBe('paul@example.com');
});
