<?php

declare(strict_types=1);

use nrv\domain\entities\Spectacle;

function unSpectacleAvecImages(array $images): Spectacle
{
    return new Spectacle(
        'id-1',
        'soiree-1',
        'Retour aux sources',
        'The Stanislas Boys',
        'Les grands classiques du rock.',
        $images,
        null,
        new DateTimeImmutable('2027-06-04 20:30'),
        'classic rock'
    );
}

it('prend la première image comme image principale', function () {
    $spectacle = unSpectacleAvecImages(['img/a.jpg', 'img/b.jpg']);

    expect($spectacle->getImagePrincipale())->toBe('img/a.jpg')
        ->and($spectacle->getImages())->toHaveCount(2);
});

it('n\'a pas d\'image principale sans image', function () {
    expect(unSpectacleAvecImages([])->getImagePrincipale())->toBeNull();
});
