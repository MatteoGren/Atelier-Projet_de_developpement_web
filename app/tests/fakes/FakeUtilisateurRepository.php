<?php

declare(strict_types=1);

namespace nrv\tests\fakes;

use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\domain\entities\Utilisateur;

// Repository en mémoire : permet de tester le service sans base de données.
class FakeUtilisateurRepository implements UtilisateurRepositoryInterface
{
    /** @var Utilisateur[] */
    public array $utilisateurs;

    /**
     * @param Utilisateur[] $utilisateurs
     */
    public function __construct(array $utilisateurs = [])
    {
        $this->utilisateurs = $utilisateurs;
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        foreach ($this->utilisateurs as $utilisateur) {
            if ($utilisateur->getEmail() === $email) {
                return $utilisateur;
            }
        }
        return null;
    }

    public function findById(string $id): ?Utilisateur
    {
        foreach ($this->utilisateurs as $utilisateur) {
            if ($utilisateur->getIdUtilisateur() === $id) {
                return $utilisateur;
            }
        }
        return null;
    }

    public function save(Utilisateur $utilisateur): void
    {
        $this->utilisateurs[] = $utilisateur;
    }
}
