<?php

declare(strict_types=1);

namespace nrv\application\validators;

use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;

class CommandeValidator
{

    public static function valider(CreerCommandeDTO $commande): void
    {
        if ($commande->lignes === []) {
            throw new CommandeInvalideException('Le panier est vide.');
        }

        $soireesVues = [];

        foreach ($commande->lignes as $ligne) {
            IdentifiantValidator::valider($ligne->idSoiree);

            if (isset($soireesVues[$ligne->idSoiree])) {
                throw new CommandeInvalideException(
                    "Soirée en double dans le panier : {$ligne->idSoiree}"
                );
            }

            $soireesVues[$ligne->idSoiree] = true;

            if ($ligne->nbNormal < 0 || $ligne->nbReduit < 0) {
                throw new CommandeInvalideException(
                    'Les quantités de billets ne peuvent pas être négatives.'
                );
            }

            if ($ligne->nbNormal + $ligne->nbReduit === 0) {
                throw new CommandeInvalideException(
                    "Aucun billet demandé pour la soirée {$ligne->idSoiree}."
                );
            }
        }
    }
}
