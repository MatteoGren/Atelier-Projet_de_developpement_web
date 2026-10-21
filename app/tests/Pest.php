<?php

declare(strict_types=1);

use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\dtos\LigneCommandeDTO;

const UTILISATEUR_TEST = '11111111-0000-0000-0000-000000000001';

// UUID de la soirée n° $numero (les soirées 1 et 2 existent dans le faux dépôt, pas la 99)
function uneSoiree(int $numero): string
{
    return sprintf('22222222-0000-0000-0000-%012d', $numero);
}

function uneLigne(int $soiree, int $nbNormal, int $nbReduit): LigneCommandeDTO
{
    return new LigneCommandeDTO(uneSoiree($soiree), $nbNormal, $nbReduit);
}

function unPanier(LigneCommandeDTO ...$lignes): CreerCommandeDTO
{
    return new CreerCommandeDTO(UTILISATEUR_TEST, $lignes);
}
