<?php

declare(strict_types=1);

namespace nrv\tests\services;

use DateTimeImmutable;
use nrv\application\dtos\SpectacleListeDTO;
use nrv\application\usecases\ServiceSpectacle;
use nrv\domain\entities\Spectacle;
use nrv\tests\fakes\FakeSpectacleRepository;
use PHPUnit\Framework\TestCase;

class ServiceSpectacleTest extends TestCase
{
    public function testListeVideSansSpectacle(): void
    {
        $service = new ServiceSpectacle(new FakeSpectacleRepository());

        $this->assertSame([], $service->listerSpectacles());
    }

    public function testListeRenvoieTitreDateHoraireImage(): void
    {
        $repository = new FakeSpectacleRepository([
            new Spectacle(
                'id-1',
                'soiree-1',
                'Highway Night',
                'Route 54',
                null,
                ['img/highway-night.jpg', 'img/autre.jpg'],
                null,
                new DateTimeImmutable('2027-06-04 22:00'),
                'classic rock'
            ),
        ]);
        $service = new ServiceSpectacle($repository);

        $liste = $service->listerSpectacles();

        $this->assertCount(1, $liste);
        $this->assertInstanceOf(SpectacleListeDTO::class, $liste[0]);
        $this->assertSame([
            'id' => 'id-1',
            'titre' => 'Highway Night',
            'date' => '2027-06-04',
            'horaire' => '22:00',
            'image' => 'img/highway-night.jpg',
        ], $liste[0]->toArray());
        $this->assertSame('soiree-1', $liste[0]->soireeId);
    }
}
