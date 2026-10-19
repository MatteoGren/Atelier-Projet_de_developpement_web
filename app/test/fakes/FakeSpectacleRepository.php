<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\domain\entities\Spectacle;

// Repository en mémoire : permet de tester le service sans base de données.
class FakeSpectacleRepository implements SpectacleRepositoryInterface
{
    /**
     * @param Spectacle[] $spectacles
     */
    public function __construct(private array $spectacles = [])
    {
    }

    public function findAll(): array
    {
        return $this->spectacles;
    }
}
