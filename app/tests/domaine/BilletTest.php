<?php

declare(strict_types=1);

use nrv\domain\entities\Billet;

function unBillet(bool $tarifReduit): Billet
{
    return new Billet('billet-1', 'commande-1', 'soiree-1', 'NRV-1', 'token-1', $tarifReduit, 25.0);
}

it('classe un billet plein tarif en catégorie normale', function () {
    expect(unBillet(false)->getCategorie())->toBe('normal');
});

it('classe un billet à tarif réduit en catégorie réduite', function () {
    expect(unBillet(true)->getCategorie())->toBe('reduit');
});
