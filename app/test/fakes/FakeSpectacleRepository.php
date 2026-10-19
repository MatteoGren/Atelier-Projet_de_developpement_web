<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\domain\entities\Spectacle;

// Repository en mémoire : permet de tester le service sans base de données.
class FakeSpectacleRepository implements SpectacleRepositoryInterface
{
    /** @var Spectacle[] */
    private array $spectacles;

    /**
     * @param Spectacle[] $spectacles
     */
    public function __construct(array $spectacles = [])
    {
        $this->spectacles = $spectacles;
    }

    public function findAll(): array
    {
        return $this->spectacles;
    }
}
