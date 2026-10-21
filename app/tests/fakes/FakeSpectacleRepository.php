<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use DateTime;
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

    public function findBySoireeId(string $soireeId): array
    {
        return array_values(array_filter(
            $this->spectacles,
            fn (Spectacle $spectacle) => $spectacle->getSoireeId() === $soireeId
        ));
    }

    public function filtrerParDate(DateTime $date): array
    {
        return array_values(array_filter(
            $this->spectacles,
            fn (Spectacle $spectacle) => $spectacle->getHoraire()->format('Y-m-d') === $date->format('Y-m-d')
        ));
    }

    public function filtrerParStyle(string $style): array
    {
        return array_values(array_filter(
            $this->spectacles,
            fn (Spectacle $spectacle) => mb_strtolower($spectacle->getStyle()) === mb_strtolower($style)
        ));
    }

    public function filtrer(DateTime $date, string $style): array
    {
        return (new self($this->filtrerParDate($date)))->filtrerParStyle($style);
    }
}
