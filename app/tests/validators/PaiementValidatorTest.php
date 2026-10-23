<?php

declare(strict_types=1);

use nrv\application\exceptions\PaiementInvalideException;
use nrv\application\validators\PaiementValidator;

it('accepte une carte valide', function (string $numeroCarte, string $dateExpiration) {
    PaiementValidator::valider(unPaiement(1, $numeroCarte, $dateExpiration));
})->throwsNoExceptions()->with([
    'carte de test' => ['4242424242424242', '12/99'],
    'valable jusqu\'à la fin du mois courant' => ['4242424242424242', date('m/y')],
    'numéro quelconque de 16 chiffres' => ['1234567812345678', '12/99'],
    'numéro avec espaces' => ['4242 4242 4242 4242', '12/99'],
]);

it('refuse une carte invalide', function (string $numeroCarte, string $dateExpiration, string $code) {
    PaiementValidator::valider(unPaiement(1, $numeroCarte, $dateExpiration, $code));
})->throws(PaiementInvalideException::class)->with([
    'numéro avec lettres' => ['4242 4242 4242 42AB', '12/99', '123'],
    'numéro de 17 chiffres' => ['42424242424242424', '12/99', '123'],
    'carte expirée' => ['4242424242424242', date('m/y', strtotime('first day of last month')), '123'],
    'code trop long' => ['4242424242424242', '12/99', '1234'],
]);
