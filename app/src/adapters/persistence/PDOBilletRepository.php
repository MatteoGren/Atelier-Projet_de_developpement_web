<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
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

        return $this->versBillet($ligne);
    }

    public function findPayesByUtilisateur(string $idUtilisateur): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.id, b.commande_id, b.soiree_id, b.ref, b.token, b.tarif_reduit, b.prix
             FROM billet b
             JOIN commande c ON c.id = b.commande_id
             JOIN soiree s ON s.id = b.soiree_id
             WHERE c.utilisateur_id = :utilisateur AND c.statut = :statut
             ORDER BY s.date, b.ref'
        );
        $stmt->execute(['utilisateur' => $idUtilisateur, 'statut' => Commande::STATUT_VALIDEE]);

        return array_map(fn (array $ligne) => $this->versBillet($ligne), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function versBillet(array $ligne): Billet
    {
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
