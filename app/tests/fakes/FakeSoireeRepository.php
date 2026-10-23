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

    /**
     * @param Soiree[] $soirees
     */
    public function __construct(array $soirees = [])
    {
        $this->soirees = $soirees;
    }

    public function findById(string $id): ?Soiree
    {
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
