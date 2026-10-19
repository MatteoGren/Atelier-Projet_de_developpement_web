<?php

namespace nrv\domain\entities;



class spectacle {

    public function __construct(
        private string $idSpectacle,
        private string $idSoiree,
        private string $titreSpectacle,
        private string $artisteSpectacle,
        private string $descriptionSpectacle,
        private string $cheminVideoSpectacle,
        private string $cheminImageSpectacle,
        private DateTime $styleSpectacle,
        private string $HoraireSpectacle,
    ) {}

    


}