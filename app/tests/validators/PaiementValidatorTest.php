<?php

declare(strict_types=1);

namespace nrv\tests\validators;

use nrv\application\dtos\PaiementDTO;
use nrv\application\exceptions\PaiementInvalideException;
use nrv\application\validators\PaiementValidator;
use PHPUnit\Framework\TestCase;

class PaiementValidatorTest extends TestCase
{
    private function paiement(string $numeroCarte = '4242424242424242', string $dateExpiration = '12/99', string $code = '123'): PaiementDTO
    {
        return new PaiementDTO('55555555-0000-0000-0000-000000000001', '11111111-0000-0000-0000-000000000001', $numeroCarte, $dateExpiration, $code);
    }

    public function testCarteValideAcceptee(): void
    {
        PaiementValidator::valider($this->paiement());
        $this->addToAssertionCount(1);
    }

    public function testCarteValableJusquALaFinDuMoisCourant(): void
    {
        PaiementValidator::valider($this->paiement(dateExpiration: date('m/y')));
        $this->addToAssertionCount(1);
    }

    public function testNumeroQuelconqueDe16ChiffresAccepte(): void
    {
        PaiementValidator::valider($this->paiement('1234567812345678'));
        $this->addToAssertionCount(1);
    }

    public function testNumeroAvecEspacesAccepte(): void
    {
        PaiementValidator::valider($this->paiement('4242 4242 4242 4242'));
        $this->addToAssertionCount(1);
    }

    public function testNumeroAvecLettresRefuse(): void
    {
        $this->expectException(PaiementInvalideException::class);
        PaiementValidator::valider($this->paiement('4242 4242 4242 42AB'));
    }

    public function testNumeroDePlusDe16ChiffresRefuse(): void
    {
        $this->expectException(PaiementInvalideException::class);
        PaiementValidator::valider($this->paiement('42424242424242424'));
    }

    public function testCarteExpireeRefusee(): void
    {
        $this->expectException(PaiementInvalideException::class);
        PaiementValidator::valider($this->paiement(dateExpiration: date('m/y', strtotime('first day of last month'))));
    }

    public function testCodeTropLongRefuse(): void
    {
        $this->expectException(PaiementInvalideException::class);
        PaiementValidator::valider($this->paiement(code: '1234'));
    }
}
