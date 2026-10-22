<?php

declare(strict_types=1);

namespace nrv\tests\services;

use DateTime;
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
            'style' => 'classic rock',
        ], $liste[0]->toArray());
        $this->assertSame('soiree-1', $liste[0]->soireeId);
    }

    public function testFiltreParStyleNeGardeQueCeStyle(): void
    {
        $service = new ServiceSpectacle($this->depotDeTrois());

        $titres = array_map(fn ($dto) => $dto->titre, $service->filtrerParStyle('rock'));

        $this->assertSame(['Néons et guitares'], $titres);
    }

    public function testFiltreParDate(): void
    {
        $service = new ServiceSpectacle($this->depotDeTrois());

        $titres = array_map(fn ($dto) => $dto->titre, $service->filtrerParDate(new DateTime('2027-06-04')));

        $this->assertSame(['Highway Night', 'Blues de la rivière'], $titres);
    }

    public function testFiltreDateEtStyle(): void
    {
        $service = new ServiceSpectacle($this->depotDeTrois());

        $liste = $service->filtrer(new DateTime('2027-06-04'), 'blues rock');

        $this->assertCount(1, $liste);
        $this->assertSame('Blues de la rivière', $liste[0]->titre);
    }

    private function depotDeTrois(): FakeSpectacleRepository
    {
        return new FakeSpectacleRepository([
            $this->unSpectacle('Highway Night', '2027-06-04 22:00', 'classic rock'),
            $this->unSpectacle('Blues de la rivière', '2027-06-04 23:00', 'blues rock'),
            $this->unSpectacle('Néons et guitares', '2027-06-13 19:30', 'rock'),
        ]);
    }

    private function unSpectacle(string $titre, string $horaire, string $style): Spectacle
    {
        return new Spectacle('id-' . $titre, 'soiree-1', $titre, 'Artiste', null, [], null, new DateTimeImmutable($horaire), $style);
    }
}
