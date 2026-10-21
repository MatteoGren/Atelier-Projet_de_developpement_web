<?php

declare(strict_types=1);

namespace nrv\tests\domaine;

use nrv\domain\entities\Billet;
use PHPUnit\Framework\TestCase;

class BilletTest extends TestCase
{
    private function creerBillet(bool $tarifReduit): Billet
    {
        return new Billet('billet-1', 'commande-1', 'soiree-1', 'NRV-1', 'token-1', $tarifReduit, 25.0);
    }

    public function testCategorieNormale(): void
    {
        $this->assertSame('normal', $this->creerBillet(false)->getCategorie());
    }

    public function testCategorieReduite(): void
    {
        $this->assertSame('reduit', $this->creerBillet(true)->getCategorie());
    }
}
