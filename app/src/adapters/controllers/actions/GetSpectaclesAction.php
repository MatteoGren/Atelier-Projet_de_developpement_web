<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use DateTime;
use nrv\application\ports\api\ServiceSpectacleInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class GetSpectaclesAction
{
    private const TAILLE_PAGE = 6;

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

        // Sans ?page : programme complet (le front en tire les listes de filtres).
        if (!array_key_exists('page', $parametres)) {
            return $this->repondre($response, [
                'type' => 'collection',
                'count' => count($spectacles),
                'spectacles' => array_map(fn ($dto) => $this->element($dto), $spectacles),
                'links' => [
                    'self' => ['href' => '/spectacles'],
                ],
            ]);
        }

        $page = $this->lirePage($request, $parametres['page']);
        $nbPages = max(1, (int) ceil(count($spectacles) / self::TAILLE_PAGE));
        if ($page > $nbPages) {
            throw new HttpNotFoundException($request, "Page $page introuvable : il y a $nbPages page(s).");
        }

        $liens = [
            'self' => ['href' => '/spectacles?page=' . $page],
            'first' => ['href' => '/spectacles?page=1'],
            'last' => ['href' => '/spectacles?page=' . $nbPages],
        ];
        if ($page > 1) {
            $liens['prev'] = ['href' => '/spectacles?page=' . ($page - 1)];
        }
        if ($page < $nbPages) {
            $liens['next'] = ['href' => '/spectacles?page=' . ($page + 1)];
        }

        $donnees = [
            'type' => 'collection',
            'count' => count($spectacles),
            'page' => $page,
            'pages' => $nbPages,
            'spectacles' => array_map(
                fn ($dto) => $this->element($dto),
                array_slice($spectacles, ($page - 1) * self::TAILLE_PAGE, self::TAILLE_PAGE)
            ),
            'links' => $liens,
        ];

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

    // Numéro de page : entier à partir de 1, sinon erreur 400.
    private function lirePage(Request $request, mixed $texte): int
    {
        if (!is_string($texte) || !preg_match('/^[1-9][0-9]{0,5}$/', $texte)) {
            throw new HttpBadRequestException($request, 'Page invalide : un entier à partir de 1 est attendu.');
        }
        return (int) $texte;
    }

    private function repondre(Response $response, array $donnees): Response
    {
        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
