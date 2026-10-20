<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\ports\api\ServiceUtilisateurInterface;


class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    
    private UtilisateurRepositoryInterface $utilisateurRepository;


    public function __construct(UtilisateurRepositoryInterface $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function connecter(string $email, string $motDePasse): Utilisateur {
        $utilisateur = $this->utilisateurRepository->FindByEmail($email);
        if (!$utilisateur) {
            throw new \InvalidArgumentException('Utilisateur non trouvé');
        }

        if (!password_verify($motDePasse, $utilisateur->getMotDePasse())) {
            throw new \InvalidArgumentException('Mot de passe incorrect');
        }
        return $utilisateur;
    }

}
