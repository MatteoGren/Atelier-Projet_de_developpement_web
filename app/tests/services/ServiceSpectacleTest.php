<?php

declare(strict_types=1);

use nrv\application\dtos\SpectacleListeDTO;
use nrv\application\usecases\ServiceSpectacle;
use nrv\domain\entities\Spectacle;
use nrv\tests\fakes\FakeSpectacleRepository;

function unSpectacleDuProgramme(string $titre, string $horaire, string $style): Spectacle
{
    return new Spectacle('id-' . $titre, 'soiree-1', $titre, 'Artiste', null, [], null, new DateTimeImmutable($horaire), $style);
}

beforeEach(function () {
    $this->spectacles = new FakeSpectacleRepository([
        unSpectacleDuProgramme('Highway Night', '2027-06-04 22:00', 'classic rock'),
        unSpectacleDuProgramme('Blues de la rivière', '2027-06-04 23:00', 'blues rock'),
        unSpectacleDuProgramme('Néons et guitares', '2027-06-13 19:30', 'rock'),
    ]);
    $this->service = new ServiceSpectacle($this->spectacles);
});

it('renvoie une liste vide sans spectacle', function () {
    expect((new ServiceSpectacle(new FakeSpectacleRepository()))->listerSpectacles())->toBe([]);
});

it('liste les spectacles avec titre, date, horaire et image principale', function () {
    $service = new ServiceSpectacle(new FakeSpectacleRepository([
        new Spectacle('id-1', 'soiree-1', 'Highway Night', 'Route 54', null, ['img/highway-night.jpg', 'img/autre.jpg'], null, new DateTimeImmutable('2027-06-04 22:00'), 'classic rock'),
    ]));

    $liste = $service->listerSpectacles();

    expect($liste)->toHaveCount(1)
        ->and($liste[0])->toBeInstanceOf(SpectacleListeDTO::class)
        ->and($liste[0]->toArray())->toBe([
            'id' => 'id-1',
            'titre' => 'Highway Night',
            'date' => '2027-06-04',
            'horaire' => '22:00',
            'image' => 'img/highway-night.jpg',
            'style' => 'classic rock',
        ])
        ->and($liste[0]->soireeId)->toBe('soiree-1');
});

it('filtre par style exact, sans garder les styles qui le contiennent', function () {
    $titres = array_map(fn ($dto) => $dto->titre, $this->service->filtrerParStyle('rock'));

    expect($titres)->toBe(['Néons et guitares']);
});

it('filtre par date', function () {
    $titres = array_map(fn ($dto) => $dto->titre, $this->service->filtrerParDate(new DateTime('2027-06-04')));

    expect($titres)->toBe(['Highway Night', 'Blues de la rivière']);
});

it('filtre par date et style à la fois', function () {
    $liste = $this->service->filtrer(new DateTime('2027-06-04'), 'blues rock');

    expect($liste)->toHaveCount(1)
        ->and($liste[0]->titre)->toBe('Blues de la rivière');
});

it('renvoie une liste vide quand aucun spectacle ne correspond', function () {
    expect($this->service->filtrerParDate(new DateTime('2030-01-01')))->toBe([]);
});

it('crée un spectacle sans soirée avec toutes ses informations', function () {
    $id = $this->service->creerSpectacle([
        'titre' => 'Nouveau',
        'artistes' => 'Groupe',
        'description' => 'Une description',
        'images' => ['img/nouveau.jpg'],
        'chemin_video' => 'https://example.com/video',
        'horaire' => '2027-06-20 21:00',
        'style' => 'metal',
    ]);

    $cree = array_values(array_filter($this->spectacles->findAll(), fn ($s) => $s->getId() === $id))[0] ?? null;
    expect($id)->not->toBeEmpty()
        ->and($cree)->not->toBeNull()
        ->and($cree->getSoireeId())->toBeNull()
        ->and($cree->getTitre())->toBe('Nouveau')
        ->and($cree->getImagePrincipale())->toBe('img/nouveau.jpg')
        ->and($cree->getVideo())->toBe('https://example.com/video')
        ->and($cree->getHoraire()->format('Y-m-d H:i'))->toBe('2027-06-20 21:00')
        ->and($cree->getStyle())->toBe('metal');
});

it('crée un spectacle rattaché à une soirée', function () {
    $this->service->creerSpectacle([
        'soiree_id' => uneSoiree(2),
        'titre' => 'Rattaché',
        'artistes' => 'Groupe',
        'horaire' => '2027-06-06 21:00',
        'style' => 'blues',
    ]);

    expect($this->spectacles->findBySoireeId(uneSoiree(2)))->toHaveCount(1);
});
