<?php

declare(strict_types=1);

use nrv\application\dtos\CreerSoireeDTO;
use nrv\application\dtos\SoireeBackOfficeDTO;
use nrv\application\dtos\SoireeDetailDTO;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\usecases\ServiceSoiree;
use nrv\domain\entities\Soiree;
use nrv\domain\entities\Spectacle;
use nrv\tests\fakes\FakeSoireeRepository;
use nrv\tests\fakes\FakeSpectacleRepository;

function unSpectacleDeSoiree(string $id, ?string $soireeId, string $titre = 'Spectacle'): Spectacle
{
    return new Spectacle($id, $soireeId, $titre, 'Artiste', null, [], null, new DateTimeImmutable('2027-06-04 20:30'), 'rock');
}

function uneDemandeDeSoiree(array $spectacles = ['libre-1', 'libre-2'], int $capacite = 300, string $date = '2027-06-20 20:00'): CreerSoireeDTO
{
    return new CreerSoireeDTO('Nouvelle soirée', 'Rock', $date, 'Nancy', $capacite, $spectacles);
}

beforeEach(function () {
    $this->soirees = new FakeSoireeRepository([
        new Soiree(uneSoiree(1), 'Ouverture Électrique', 'Classic rock', new DateTimeImmutable('2027-06-04 20:00'), 'Place Stanislas', 800, 3, 65.0),
        new Soiree(uneSoiree(2), 'Blues sur la Meurthe', 'Blues', new DateTimeImmutable('2027-06-06 20:00'), 'Péniche', 500),
    ]);
    $this->spectacles = new FakeSpectacleRepository([
        new Spectacle(
            'spectacle-1',
            uneSoiree(1),
            'Retour aux sources',
            'The Stanislas Boys',
            'Les grands classiques du rock des années 70.',
            ['img/retour-aux-sources-1.jpg'],
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            new DateTimeImmutable('2027-06-04 20:30'),
            'classic rock'
        ),
        unSpectacleDeSoiree('spectacle-2', uneSoiree(2), 'Blues de la rivière'),
        unSpectacleDeSoiree('libre-1', null),
        unSpectacleDeSoiree('libre-2', null),
        unSpectacleDeSoiree('libre-3', null),
    ]);
    $this->service = new ServiceSoiree($this->soirees, $this->spectacles);
});

it('affiche le détail d\'une soirée avec ses tarifs et uniquement ses spectacles', function () {
    $detail = $this->service->afficherSoiree(uneSoiree(1));

    expect($detail)->toBeInstanceOf(SoireeDetailDTO::class)
        ->and($detail->toArray())->toBe([
            'id' => uneSoiree(1),
            'nom' => 'Ouverture Électrique',
            'thematique' => 'Classic rock',
            'date' => '2027-06-04',
            'horaire' => '20:00',
            'lieu' => 'Place Stanislas',
            'tarifs' => ['normal' => 25.0, 'reduit' => 15.0],
            'spectacles' => [
                [
                    'id' => 'spectacle-1',
                    'titre' => 'Retour aux sources',
                    'artistes' => 'The Stanislas Boys',
                    'description' => 'Les grands classiques du rock des années 70.',
                    'style' => 'classic rock',
                    'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                ],
            ],
        ]);
});

it('refuse d\'afficher une soirée inexistante', function () {
    $this->service->afficherSoiree(uneSoiree(99));
})->throws(SoireeIntrouvableException::class);

it('refuse un identifiant de soirée qui n\'est pas un UUID', function () {
    $this->service->afficherSoiree('pas-un-uuid');
})->throws(IdentifiantInvalideException::class);

it('crée une soirée et lui rattache ses spectacles', function (array $spectacles) {
    $id = $this->service->creerSoiree(uneDemandeDeSoiree($spectacles));

    $soiree = $this->soirees->findById($id);
    expect($soiree)->not->toBeNull()
        ->and($soiree->getNom())->toBe('Nouvelle soirée')
        ->and($soiree->getCapacite())->toBe(300)
        ->and($soiree->getPlacesVendues())->toBe(0)
        ->and($soiree->getDate()->format('Y-m-d H:i'))->toBe('2027-06-20 20:00');

    $rattaches = array_map(fn ($s) => $s->getId(), $this->spectacles->findBySoireeId($id));
    expect($rattaches)->toBe($spectacles);
})->with([
    '2 spectacles' => [['libre-1', 'libre-2']],
    '3 spectacles' => [['libre-1', 'libre-2', 'libre-3']],
]);

it('donne un UUID à la soirée créée', function () {
    $id = $this->service->creerSoiree(uneDemandeDeSoiree());

    expect($id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('refuse une soirée invalide et n\'enregistre rien', function (CreerSoireeDTO $demande) {
    expect(fn () => $this->service->creerSoiree($demande))->toThrow(InvalidArgumentException::class);

    expect($this->soirees->findAll())->toHaveCount(2);
})->with([
    'capacité nulle' => fn () => uneDemandeDeSoiree(capacite: 0),
    'capacité négative' => fn () => uneDemandeDeSoiree(capacite: -10),
    'un seul spectacle' => fn () => uneDemandeDeSoiree(['libre-1']),
    'quatre spectacles' => fn () => uneDemandeDeSoiree(['libre-1', 'libre-2', 'libre-3', 'spectacle-2']),
    'date invalide' => fn () => uneDemandeDeSoiree(date: 'pas une date'),
]);

it('liste les soirées avec places vendues, capacité et recette pour le tableau de bord', function () {
    $liste = $this->service->listerSoireeBackOffice();

    expect($liste)->toHaveCount(2)
        ->and($liste[0])->toBeInstanceOf(SoireeBackOfficeDTO::class)
        ->and(array_map(fn ($dto) => $dto->toArray(), $liste))->toBe([
            ['id' => uneSoiree(1), 'nom' => 'Ouverture Électrique', 'placesVendues' => 3, 'capacite' => 800, 'recette' => 65.0],
            ['id' => uneSoiree(2), 'nom' => 'Blues sur la Meurthe', 'placesVendues' => 0, 'capacite' => 500, 'recette' => 0.0],
        ]);
});
