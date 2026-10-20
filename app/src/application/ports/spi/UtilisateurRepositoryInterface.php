<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Utilisateur;



interface UtilisateurRepositoryInterface
{ 

    public function findByEmail(string $email): ?Utilisateur;

    public function save(Utilisateur $utilisateur): void;

}
