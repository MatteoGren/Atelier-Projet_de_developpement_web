<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Utilisateur;



interface UtilisateurRepositoryInterface
{ 

    public function findByEmail(string $email): ?Utilisateur;

    /**
     * @return Utilisateur|null null si aucun utilisateur n'a cet identifiant
     */
    public function findById(string $id): ?Utilisateur;

    public function save(Utilisateur $utilisateur): void;

}
