<?php

declare(strict_types=1);

namespace nrv\application\exceptions;

use Exception;

class SoireeIntrouvableException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Soirée introuvable : $id");
    }
}
