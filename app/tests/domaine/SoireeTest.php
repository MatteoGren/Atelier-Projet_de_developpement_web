<?php

declare(strict_types=1);

namespace nrv\tests\domaine;

use DateTimeImmutable;
use nrv\domain\entities\Soiree;
use PHPUnit\Framework\TestCase;

class SoireeTest extends TestCase
{
    private function creerSoiree(): Soiree
    {
        return new Soiree(
            'soiree-1',
            'Ouverture Électrique',
            'Classic rock',
            new DateTimeImmutable('2027-06-04 20:00'),
            'Place Stanislas',
            800
        );
    }

    public function testTarifsNormalEtReduit(): void
    {
        $this->assertSame(
            ['normal' => 25.0, 'reduit' => 15.0],
            $this->creerSoiree()->getTarifs()
        );
    }

    public function testTarifReduitInferieurAuTarifNormal(): void
    {
        $this->assertLessThan(Soiree::TARIF_NORMAL, Soiree::TARIF_REDUIT);
    }

    public function testDateContientJourEtHoraireDeDebut(): void
    {
        $soiree = $this->creerSoiree();

        $this->assertSame('2027-06-04', $soiree->getDate()->format('Y-m-d'));
        $this->assertSame('20:00', $soiree->getDate()->format('H:i'));
    }
}
