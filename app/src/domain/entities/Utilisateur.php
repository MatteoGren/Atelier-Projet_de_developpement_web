<?php

namespace nrv\domain\entities;


class Utilisateur {

    public function __construct(
        private string $idUtilisateur,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $telephone,
        private string $motDePasse,
        private string $role
    ) {}



    public function getIdUtilisateur(): string {
        return $this->idUtilisateur;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTelephone(): string {
        return $this->telephone;
    }

    public function getMotDePasse(): string {
        return $this->motDePasse;
    }

    public function getRole(): string {
        return $this->role;
    }




}