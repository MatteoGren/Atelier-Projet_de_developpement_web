<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\domain\entities\Soiree;

// Repository en mémoire : permet de tester le service sans base de données.
class FakeSoireeRepository implements SoireeRepositoryInterface
{
    /** @var Soiree[] */
    private array $soirees;

    // Simule une base hors service (erreur PDO) pour vérifier qu'aucun détail ne fuit vers le client.
    public bool $enPanne = false;

    /**
     * @param Soiree[] $soirees
     */
    public function __construct(array $soirees = [])
    {
        $this->soirees = $soirees;
    }

    public function findById(string $id): ?Soiree
    {
        if ($this->enPanne) {
            throw new \RuntimeException('SQLSTATE[08006] connexion à nrv.db refusée (PDOSoireeRepository.php:42)');
        }
        foreach ($this->soirees as $soiree) {
            if ($soiree->getId() === $id) {
                return $soiree;
            }
        }
        return null;
    }

    public function save(Soiree $soiree): void
    {
        $this->soirees[] = $soiree;
    }

    public function chargerRecette(Soiree $soiree): float
    {
        return 0.0;
    }

    public function findAll(): array
    {
        return $this->soirees;
    }
}
