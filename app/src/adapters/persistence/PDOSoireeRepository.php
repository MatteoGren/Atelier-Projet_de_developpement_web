<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use DateTimeImmutable;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\domain\entities\Soiree;
use PDO;

class PDOSoireeRepository implements SoireeRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Soiree
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nom, thematique, date, lieu, capacite, places_vendues
             FROM soiree
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return new Soiree(
            $ligne['id'],
            $ligne['nom'],
            $ligne['thematique'],
            new DateTimeImmutable($ligne['date']),
            $ligne['lieu'],
            (int) $ligne['capacite'],
            (int) $ligne['places_vendues']
        );
    }
}
