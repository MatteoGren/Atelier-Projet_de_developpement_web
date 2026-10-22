<?php

declare(strict_types=1);

namespace nrv\application\exceptions;

use Exception;

class BilletIntrouvableException extends Exception
{
    // Le token n'est pas répété dans le message : c'est la clé d'accès au billet.
    public function __construct()
    {
        parent::__construct('Billet introuvable');
    }
}
