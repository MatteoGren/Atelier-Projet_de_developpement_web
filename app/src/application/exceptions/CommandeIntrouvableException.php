<?php

declare(strict_types=1);

namespace nrv\application\exceptions;

use Exception;

class CommandeIntrouvableException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Commande introuvable : $id");
    }
}
