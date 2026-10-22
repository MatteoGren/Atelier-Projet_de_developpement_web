<?php

declare(strict_types=1);

namespace nrv\domain\exceptions;

use Exception;

class CommandeNonPayableException extends Exception
{
    public function __construct(string $idCommande)
    {
        parent::__construct("La commande n'est plus en attente de paiement : $idCommande");
    }
}
