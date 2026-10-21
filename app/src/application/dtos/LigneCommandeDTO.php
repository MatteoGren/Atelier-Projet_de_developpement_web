<?php

declare(strict_types=1);

namespace nrv\application\dtos;

// Une ligne du panier (une soirée et le nombre de billets par tarif)
final class LigneCommandeDTO
{
    public readonly string $idSoiree;
    public readonly int $nbNormal;
    public readonly int $nbReduit;

    public function __construct(string $idSoiree, int $nbNormal, int $nbReduit)
    {
        $this->idSoiree = $idSoiree;
        $this->nbNormal = $nbNormal;
        $this->nbReduit = $nbReduit;
    }
}
