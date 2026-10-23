<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\application\dtos\BilletDTO;

interface ServiceBilletInterface
{
    public function afficherBilletsCommande(string $idCommande, string $idUtilisateur): array;

    public function afficherBilletsUtilisateur(string $idUtilisateur): array;

    public function afficherBillet(string $token): BilletDTO;
}
