<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use DateTime;
use Exception;
use nrv\application\ports\api\ServiceSpectacleInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class GetSpectaclesAction
{
    private ServiceSpectacleInterface $serviceSpectacle;

    public function __construct(ServiceSpectacleInterface $serviceSpectacle)
    {
        $this->serviceSpectacle = $serviceSpectacle;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $parametres = $request->getQueryParams();

        // Présence de date ou style (même vides) = recherche filtrée, renvoyée en entier.
        if (array_key_exists('date', $parametres) || array_key_exists('style', $parametres)) {
            return $this->filtrer($request, $response, $parametres);
        }

        $spectacles = $this->serviceSpectacle->listerSpectacles();
        $tabSpectacles = [];
        $taillePage = 0;
        $tailleTab = 0;

        foreach($spectacles as $spectacle){
            if($taillePage > 5){
                $taillePage = 0;
                $tailleTab += 1;
            }
            $tabSpectacles[$tailleTab][$taillePage] = $spectacle;
            $taillePage += 1;
        }

        $parametres = $request->getQueryParams();
        $requestIndex = (int) ($parametres['page'] ?? '');
        if($requestIndex > count($tabSpectacles)){
            throw new Exception('Index out of bounds');
        }
        if($requestIndex != '' && $requestIndex != null){
            $donnees = [

            'type' => 'collection',
            'count' => count($spectacles),
            'spectacles' => array_map(fn ($dto) => $this->element($dto), $tabSpectacles[$requestIndex - 1]),
            'links' => [
                'self' => ['href' => '/spectacles'],
            ],
        ];
        }else{
            $donnees = [
            'type' => 'collection',
            'count' => count($spectacles),
            'spectacles' => array_map(fn ($dto) => [
                'spectacle' => array_merge($dto->toArray(), [
                    'soiree_id' => $dto->soireeId,
                ]),
                'links' => [
                    'soiree' => $dto->soireeId === null
                        ? null
                        : ['href' => '/soirees/' . $dto->soireeId],
                ],
            ], $spectacles),
            'links' => [
                'self' => ['href' => '/spectacles'],
            ],
        ];
        }
        
        

        return $this->repondre($response, $donnees);
    }

    private function filtrer(Request $request, Response $response, array $parametres): Response
    {
        $requestDate = trim((string) ($parametres['date'] ?? ''));
        $requestStyleMusique = trim((string) ($parametres['style'] ?? ''));

        // Paramètre vide = filtre non utilisé (« Toutes les dates », « Tous les styles »).
        $date = $requestDate === '' ? null : $this->lireDate($request, $requestDate);

        if ($date !== null && $requestStyleMusique !== '') {
            $dtos = $this->serviceSpectacle->filtrer($date, $requestStyleMusique);
        } elseif ($date !== null) {
            $dtos = $this->serviceSpectacle->filtrerParDate($date);
        } elseif ($requestStyleMusique !== '') {
            $dtos = $this->serviceSpectacle->filtrerParStyle($requestStyleMusique);
        } else {
            $dtos = $this->serviceSpectacle->listerSpectacles();
        }

        $requete = http_build_query(array_filter([
            'date' => $requestDate,
            'style' => $requestStyleMusique,
        ]));

        $donnees = [
            'type' => 'collection',
            'count' => count($dtos),
            'spectacles' => array_map(fn ($dto) => $this->element($dto), $dtos),
            'links' => [
                'self' => ['href' => '/spectacles' . ($requete === '' ? '' : '?' . $requete)],
                'spectacles' => ['href' => '/spectacles'],
            ],
        ];

        return $this->repondre($response, $donnees);
    }

    private function element(object $dto): array
    {
        return [
            'spectacle' => array_merge($dto->toArray(), [
                'soiree_id' => $dto->soireeId,
            ]),
            'links' => [
                'soiree' => $dto->soireeId === null
                    ? null
                    : ['href' => '/soirees/' . $dto->soireeId],
            ],
        ];
    }

    // Date attendue au format AAAA-MM-JJ, sinon erreur 400 (rendue en JSON par Slim).
    private function lireDate(Request $request, string $texte): DateTime
    {
        $date = DateTime::createFromFormat('!Y-m-d', $texte);
        if ($date === false || $date->format('Y-m-d') !== $texte) {
            throw new HttpBadRequestException($request, 'Date invalide : format attendu AAAA-MM-JJ.');
        }
        return $date;
    }

    private function repondre(Response $response, array $donnees): Response
    {
        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
