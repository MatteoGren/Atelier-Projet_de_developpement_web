<?php

declare(strict_types=1);

namespace nrv\application\exceptions;

use Exception;

class MotDePasseInvalideException extends Exception
{
    public function __construct(string $email)
    {
        parent::__construct("Mot de passe invalide pour l'utilisateur : $email");
    }
}
