<?php

declare(strict_types=1);

use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\dtos\LigneCommandeDTO;
use nrv\application\dtos\PaiementDTO;

const UTILISATEUR_TEST = '11111111-0000-0000-0000-000000000001';

// Comptes de l'API en mémoire (tests/fakes/ApiEnMemoire.php), mot de passe : secret123
const CLIENT = UTILISATEUR_TEST;
const AUTRE_CLIENT = '11111111-0000-0000-0000-000000000002';
const ORGANISATEUR = '11111111-0000-0000-0000-000000000003';
const ADMINISTRATEUR = '11111111-0000-0000-0000-000000000004';

// Corps JSON d'une réponse de l'API, décodé en tableau.
function corps(Psr\Http\Message\ResponseInterface $reponse): array
{
    return json_decode((string) $reponse->getBody(), true);
}

// UUID de la soirée n° $numero (1 et 2 existent dans le faux dépôt, 3 est complète, la 99 n'existe pas)
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

// UUID de la commande n° $numero (préparées dans le faux dépôt de ServiceCommandeTest)
function uneCommande(int $numero): string
{
    return sprintf('55555555-0000-0000-0000-%012d', $numero);
}

// Carte de test valide par défaut (expiration lointaine)
function unPaiement(int $commande, string $numeroCarte = '4242424242424242', string $dateExpiration = '12/99', string $code = '123'): PaiementDTO
{
    return new PaiementDTO(uneCommande($commande), UTILISATEUR_TEST, $numeroCarte, $dateExpiration, $code);
}
