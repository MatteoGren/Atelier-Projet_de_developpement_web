<?php

declare(strict_types=1);

use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\validators\IdentifiantValidator;

it('accepte un UUID', function () {
    IdentifiantValidator::valider('22222222-0000-0000-0000-000000000001');
})->throwsNoExceptions();

it('refuse un identifiant qui n\'est pas un UUID', function (string $identifiant) {
    IdentifiantValidator::valider($identifiant);
})->throws(IdentifiantInvalideException::class)->with([
    'texte quelconque' => 'abc',
    'chaîne vide' => '',
    'injection SQL' => "1' OR '1'='1",
    'UUID tronqué' => '22222222-0000-0000-0000-00000000000',
]);
