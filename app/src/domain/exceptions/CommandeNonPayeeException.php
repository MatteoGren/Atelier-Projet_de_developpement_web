<?php

declare(strict_types=1);

namespace nrv\domain\exceptions;

use Exception;

class CommandeNonPayeeException extends Exception
{
    public function __construct(string $idCommande)
    {
        parent::__construct("La commande n'est pas encore payée : $idCommande");
    }
}
