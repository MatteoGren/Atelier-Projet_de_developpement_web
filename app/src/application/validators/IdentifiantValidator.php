<?php

declare(strict_types=1);

namespace nrv\application\validators;

use nrv\application\exceptions\IdentifiantInvalideException;
use Ramsey\Uuid\Uuid;

class IdentifiantValidator
{
    public static function valider(string $id): void
    {
        if (!Uuid::isValid($id)) {
            throw new IdentifiantInvalideException($id);
        }
    }
}
