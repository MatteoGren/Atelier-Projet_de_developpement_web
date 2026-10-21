<?php

declare(strict_types=1);

namespace nrv\domain\exceptions;

use Exception;

class PlacesInsuffisantesException extends Exception
{
    public function __construct(string $soiree)
    {
        parent::__construct("Plus assez de places disponibles pour la soirée : $soiree");
    }
}
