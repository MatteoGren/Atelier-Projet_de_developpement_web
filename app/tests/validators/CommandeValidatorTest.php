<?php

declare(strict_types=1);

use nrv\application\dtos\LigneCommandeDTO;
use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\validators\CommandeValidator;

it('accepte un panier de plusieurs soirées', function () {
    CommandeValidator::valider(unPanier(uneLigne(1, 2, 0), uneLigne(2, 0, 1)));
})->throwsNoExceptions();

it('refuse un panier invalide', function (array $lignes, string $exception) {
    expect(fn () => CommandeValidator::valider(unPanier(...$lignes)))->toThrow($exception);
})->with([
    'panier vide' => [[], CommandeInvalideException::class],
    'quantité normale négative' => [[uneLigne(1, -1, 2)], CommandeInvalideException::class],
    'quantité réduite négative' => [[uneLigne(1, 2, -1)], CommandeInvalideException::class],
    'ligne sans billet' => [[uneLigne(1, 0, 0)], CommandeInvalideException::class],
    'soirée en double' => [[uneLigne(1, 1, 0), uneLigne(1, 0, 1)], CommandeInvalideException::class],
    'identifiant de soirée invalide' => [[new LigneCommandeDTO('pas-un-uuid', 1, 0)], IdentifiantInvalideException::class],
]);
