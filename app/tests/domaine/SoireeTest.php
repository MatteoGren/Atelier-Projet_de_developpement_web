<?php

declare(strict_types=1);

use nrv\domain\entities\Soiree;
use nrv\domain\exceptions\PlacesInsuffisantesException;

function uneSoireeDe(int $capacite, int $placesVendues = 0): Soiree
{
    return new Soiree('soiree-1', 'Ouverture Électrique', 'Classic rock', new DateTimeImmutable('2027-06-04 20:00'), 'Place Stanislas', $capacite, $placesVendues);
}

it('applique les tarifs normal et réduit du festival', function () {
    expect(uneSoireeDe(800)->getTarifs())->toBe(['normal' => 25.0, 'reduit' => 15.0]);
});

it('offre toute la capacité tant que rien n\'est vendu', function () {
    expect(uneSoireeDe(800)->placesDisponibles())->toBe(800);
});

it('retire les places vendues de la capacité', function () {
    expect(uneSoireeDe(4, 3)->placesDisponibles())->toBe(1);
});

it('accepte une commande qui tient dans les places restantes', function () {
    uneSoireeDe(4, 3)->verifierPlaces(1);
})->throwsNoExceptions();

it('refuse une commande qui dépasse les places restantes', function () {
    uneSoireeDe(4, 3)->verifierPlaces(2);
})->throws(PlacesInsuffisantesException::class);
