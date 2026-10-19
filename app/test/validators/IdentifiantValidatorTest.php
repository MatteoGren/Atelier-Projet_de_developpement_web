<?php

declare(strict_types=1);

namespace nrv\tests\validators;

use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\validators\IdentifiantValidator;
use PHPUnit\Framework\TestCase;

class IdentifiantValidatorTest extends TestCase
{
    public function testUuidValideAccepte(): void
    {
        $this->expectNotToPerformAssertions();

        IdentifiantValidator::valider('22222222-0000-0000-0000-000000000001');
    }

    public function testTexteQuelconqueRefuse(): void
    {
        $this->expectException(IdentifiantInvalideException::class);

        IdentifiantValidator::valider('abc');
    }

    public function testInjectionSqlRefusee(): void
    {
        $this->expectException(IdentifiantInvalideException::class);

        IdentifiantValidator::valider("1' OR '1'='1");
    }
}
