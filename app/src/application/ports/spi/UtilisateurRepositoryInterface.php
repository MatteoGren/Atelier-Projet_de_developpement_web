<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;




interface UtilisateurRepositoryInterface
{

    public function FindByEmail(string $email): ?Utilisateur;

    public function save(Utilisateur $utilisateur): void;

}
