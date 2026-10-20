<?php

declare(strict_types=1);

namespace nrv\application\exceptions;

use Exception;

class UtilisateurIntrouvableException extends Exception
{
    public function __construct(string $email)
    {
        parent::__construct("Utilisateur introuvable : $email");
    }
}
