<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\domain\entities\Billet;
use PDO;

class PDOBilletRepository implements BilletRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByToken(string $token): ?Billet
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, commande_id, soiree_id, ref, token, tarif_reduit, prix
             FROM billet
             WHERE token = :token'
        );
        $stmt->execute(['token' => $token]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return new Billet(
            $ligne['id'],
            $ligne['commande_id'],
            $ligne['soiree_id'],
            $ligne['ref'],
            $ligne['token'],
            (bool) $ligne['tarif_reduit'],
            (float) $ligne['prix']
        );
    }
}
