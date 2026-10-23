<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\application\dtos\CommandeDTO;
use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\dtos\PaiementDTO;

interface ServiceCommandeInterface
{
    public function creerCommande(CreerCommandeDTO $commande): CommandeDTO;

    public function afficherCommande(string $idCommande, string $idUtilisateur): CommandeDTO;

    public function payerCommande(PaiementDTO $paiement): CommandeDTO;
}
