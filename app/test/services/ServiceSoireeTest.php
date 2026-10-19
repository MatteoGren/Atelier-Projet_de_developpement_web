<?php

declare(strict_types=1);

namespace nrv\tests\services;

use DateTimeImmutable;
use nrv\application\dtos\SoireeDetailDTO;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\usecases\ServiceSoiree;
use nrv\domain\entities\Soiree;
use nrv\domain\entities\Spectacle;
use nrv\tests\fakes\FakeSoireeRepository;
use nrv\tests\fakes\FakeSpectacleRepository;
use PHPUnit\Framework\TestCase;

class ServiceSoireeTest extends TestCase
{
    private const ID_SOIREE = '22222222-0000-0000-0000-000000000001';
    private const ID_AUTRE_SOIREE = '22222222-0000-0000-0000-000000000002';

    private function creerService(): ServiceSoiree
    {
        $soirees = new FakeSoireeRepository([
            new Soiree(
                self::ID_SOIREE,
                'Ouverture Électrique',
                'Classic rock',
                new DateTimeImmutable('2027-06-04 20:00'),
                'Place Stanislas',
                800
            ),
        ]);
        $spectacles = new FakeSpectacleRepository([
            new Spectacle(
                'spectacle-1',
                self::ID_SOIREE,
                'Retour aux sources',
                'The Stanislas Boys',
                'Les grands classiques du rock des années 70.',
                ['img/retour-aux-sources-1.jpg'],
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                new DateTimeImmutable('2027-06-04 20:30'),
                'classic rock'
            ),
            new Spectacle(
                'spectacle-2',
                self::ID_AUTRE_SOIREE,
                'Blues de la rivière',
                'Blue Meurthe',
                null,
                [],
                null,
                new DateTimeImmutable('2027-06-06 20:00'),
                'blues rock'
            ),
        ]);

        return new ServiceSoiree($soirees, $spectacles);
    }

    public function testDetailContientInfosTarifsEtSpectaclesDeLaSoiree(): void
    {
        $detail = $this->creerService()->afficherSoiree(self::ID_SOIREE);

        $this->assertInstanceOf(SoireeDetailDTO::class, $detail);
        $this->assertSame([
            'id' => self::ID_SOIREE,
            'nom' => 'Ouverture Électrique',
            'thematique' => 'Classic rock',
            'date' => '2027-06-04',
            'horaire' => '20:00',
            'lieu' => 'Place Stanislas',
            'tarifs' => ['normal' => 25.0, 'reduit' => 15.0],
            'spectacles' => [
                [
                    'id' => 'spectacle-1',
                    'titre' => 'Retour aux sources',
                    'artistes' => 'The Stanislas Boys',
                    'description' => 'Les grands classiques du rock des années 70.',
                    'style' => 'classic rock',
                    'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                ],
            ],
        ], $detail->toArray());
    }

    public function testSoireeInexistanteLeveUneException(): void
    {
        $this->expectException(SoireeIntrouvableException::class);

        $this->creerService()->afficherSoiree('22222222-0000-0000-0000-000000000099');
    }

    public function testIdentifiantInvalideLeveUneException(): void
    {
        $this->expectException(IdentifiantInvalideException::class);

        $this->creerService()->afficherSoiree('pas-un-uuid');
    }
}
