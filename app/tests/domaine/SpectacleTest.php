<?php

declare(strict_types=1);

namespace nrv\tests\domaine;

use DateTimeImmutable;
use nrv\domain\entities\Spectacle;
use PHPUnit\Framework\TestCase;

class SpectacleTest extends TestCase
{
    private function creerSpectacle(array $images): Spectacle
    {
        return new Spectacle(
            'id-1',
            'soiree-1',
            'Retour aux sources',
            'The Stanislas Boys',
            'Les grands classiques du rock.',
            $images,
            null,
            new DateTimeImmutable('2027-06-04 20:30'),
            'classic rock'
        );
    }

    public function testImagePrincipaleEstLaPremiereImage(): void
    {
        $spectacle = $this->creerSpectacle(['img/a.jpg', 'img/b.jpg']);

        $this->assertSame('img/a.jpg', $spectacle->getImagePrincipale());
        $this->assertCount(2, $spectacle->getImages());
    }

    public function testImagePrincipaleNulleSansImage(): void
    {
        $spectacle = $this->creerSpectacle([]);

        $this->assertNull($spectacle->getImagePrincipale());
    }

    public function testHoraireContientDateEtHeure(): void
    {
        $spectacle = $this->creerSpectacle([]);

        $this->assertSame('2027-06-04', $spectacle->getHoraire()->format('Y-m-d'));
        $this->assertSame('20:30', $spectacle->getHoraire()->format('H:i'));
    }
}
