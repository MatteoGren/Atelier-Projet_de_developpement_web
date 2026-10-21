<?php

declare(strict_types=1);

namespace nrv\tests\domaine;

use DateTimeImmutable;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\exceptions\CommandeNonPayableException;
use PHPUnit\Framework\TestCase;

class CommandeTest extends TestCase
{
    private function creerCommande(string $statut = Commande::STATUT_EN_ATTENTE): Commande
    {
        $commande = new Commande('commande-1', 'utilisateur-1', $statut);
        $commande->ajouterBillet(new Billet('billet-1', 'commande-1', 'soiree-1', 'NRV-1', 'token-1', false, 25.0));
        $commande->ajouterBillet(new Billet('billet-2', 'commande-1', 'soiree-1', 'NRV-2', 'token-2', true, 15.0));
        $commande->ajouterBillet(new Billet('billet-3', 'commande-1', 'soiree-2', 'NRV-3', 'token-3', false, 25.0));
        return $commande;
    }

    public function testBilletsComptesParSoiree(): void
    {
        $this->assertSame(['soiree-1' => 2, 'soiree-2' => 1], $this->creerCommande()->billetsParSoiree());
    }

    public function testPayerValideLaCommande(): void
    {
        $commande = $this->creerCommande();
        $date = new DateTimeImmutable('2026-10-21 12:00');

        $commande->payer($date);

        $this->assertSame(Commande::STATUT_VALIDEE, $commande->getStatut());
        $this->assertSame($date, $commande->getDatePaiement());
    }

    public function testDoublePaiementRefuse(): void
    {
        $commande = $this->creerCommande();
        $commande->payer(new DateTimeImmutable());

        $this->expectException(CommandeNonPayableException::class);
        $commande->payer(new DateTimeImmutable());
    }

    public function testCommandeAnnuleeNonPayable(): void
    {
        $this->expectException(CommandeNonPayableException::class);
        $this->creerCommande(Commande::STATUT_ANNULEE)->verifierPayable();
    }
}
