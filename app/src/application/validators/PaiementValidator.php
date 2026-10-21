<?php

declare(strict_types=1);

namespace nrv\application\validators;

use DateTimeImmutable;
use nrv\application\dtos\PaiementDTO;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\PaiementInvalideException;

class PaiementValidator
{
    /**
     * @throws IdentifiantInvalideException
     * @throws PaiementInvalideException
     */
    public static function valider(PaiementDTO $paiement): void
    {
        IdentifiantValidator::valider($paiement->idCommande);

        if (!preg_match('/^\d{16}$/', $paiement->numeroCarte) || !self::cleLuhnValide($paiement->numeroCarte)) {
            throw new PaiementInvalideException('Numéro de carte invalide : 16 chiffres attendus.');
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $paiement->dateExpiration, $morceaux)) {
            throw new PaiementInvalideException('Date d\'expiration invalide : format MM/AA attendu.');
        }

        // AA vaut toujours 20AA (le format 'y' de PHP lirait 99 comme 1999).
        // Une carte reste valable jusqu'au dernier jour de son mois d'expiration.
        $finDeValidite = DateTimeImmutable::createFromFormat('!Y-m', '20' . $morceaux[2] . '-' . $morceaux[1])
            ->modify('last day of this month 23:59:59');
        if ($finDeValidite < new DateTimeImmutable()) {
            throw new PaiementInvalideException('La carte est expirée.');
        }

        if (!preg_match('/^\d{3}$/', $paiement->code)) {
            throw new PaiementInvalideException('Code de sécurité invalide : 3 chiffres attendus.');
        }
    }

    // Clé de Luhn : contrôle de saisie présent dans tous les numéros de carte bancaire.
    private static function cleLuhnValide(string $numero): bool
    {
        $somme = 0;
        $chiffres = strrev($numero);

        for ($i = 0; $i < strlen($chiffres); $i++) {
            $chiffre = (int) $chiffres[$i];
            if ($i % 2 === 1) {
                $chiffre *= 2;
                if ($chiffre > 9) {
                    $chiffre -= 9;
                }
            }
            $somme += $chiffre;
        }

        return $somme % 10 === 0;
    }
}
