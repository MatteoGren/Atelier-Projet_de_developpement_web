<?php

declare(strict_types=1);

namespace nrv\application\exceptions;

use Exception;

class IdentifiantInvalideException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Identifiant invalide : $id");
    }
}
