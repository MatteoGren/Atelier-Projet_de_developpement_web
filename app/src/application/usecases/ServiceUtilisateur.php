<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\exceptions\MotDePasseInvalideException;
use nrv\domain\entities\Utilisateur;
use nrv\application\ports\api\ServiceUtilisateurInterface;
use InvalidArgumentException;


class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    
    private UtilisateurRepositoryInterface $utilisateurRepository;


    public function __construct(UtilisateurRepositoryInterface $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function connecter(string $email, string $motDePasse): Utilisateur {
        $utilisateur = $this->utilisateurRepository->findByEmail($email);
        if (!$utilisateur) {
            throw new UtilisateurIntrouvableException($email);        
        }

        if (!password_verify($motDePasse, $utilisateur->getMotDePasse())) {
            throw new MotDePasseInvalideException($email);        
        }

        return $utilisateur;
    }




    public function register(string $nom, string $prenom, string $email, string $telephone, string $motDePasse, string $role): void {
        $existant = $this->utilisateurRepository->findByEmail($email);
        if ($existant) {
            throw new \InvalidArgumentException("Un compte existe déjà avec cet email : $email");
        }

        $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);

        $idUtilisateur = bin2hex(random_bytes(16));

        $nouvelUtilisateur = new Utilisateur(
            $idUtilisateur,
            $nom,
            $prenom,
            $email,
            $telephone,
            $motDePasseHache,
            $role
        );

        $this->utilisateurRepository->save($nouvelUtilisateur);
    }

}
