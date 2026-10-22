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

    public function findAll(): array{
        $stmt = $this->pdo->query('SELECT * FROM soiree');
        $soirees = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $soiree = new Soiree(
                $row['id'],
                $row['nom'],
                $row['thematique'],
                new DateTimeImmutable($row['date']),
                $row['lieu'],
                (int) $row['capacite'],
                (int) $row['places_vendues']
            );
            $soiree->setRecette($this->chargerRecette($soiree));   //on charge d'abord la recette avant de retourner la soiree

            $soirees[] = $soiree;
        }
        return $soirees;
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
        $soiree = new Soiree(
            $ligne['id'],
            $ligne['nom'],
            $ligne['thematique'],
            new DateTimeImmutable($ligne['date']),
            $ligne['lieu'],
            (int) $ligne['capacite'],
            (int) $ligne['places_vendues']
        );
        $soiree->setRecette($this->chargerRecette($soiree));   //on charge d'abord la recette avant de retourner la soiree

        return $soiree;
    }

    public function chargerRecette(Soiree $soiree): int{
         $stmt = $this->pdo->prepare(
            'SELECT SUM(prix)
             FROM billet
             WHERE soiree_id = :id
             AND commande_id IN (SELECT id
                                FROM commande
                                WHERE statut = :statut)'
        );

        $stmt->execute([
            'id'=> $soiree->getId(),
            'statut'=> 'validee'
            ]);
        
        return (int)$stmt->fetchColumn();
    }
}
