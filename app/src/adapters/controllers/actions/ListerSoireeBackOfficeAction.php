<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceSoireeInterface;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ListerSoireeBackOfficeAction{

    private ServiceSoireeInterface $serviceSoiree;

     public function __construct(ServiceSoireeInterface $serviceSoiree)
    {
        $this->serviceSoiree = $serviceSoiree;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
    

        // Exceptions applicatives traduites en erreurs HTTP Slim (400 / 404, rendues en JSON).
        try {
            $soirees = $this->serviceSoiree->listerSoireeBackOffice();
        } catch (IdentifiantInvalideException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        } catch (SoireeIntrouvableException $e) {
            throw new HttpNotFoundException($request, $e->getMessage(), $e);
        }

        $donnees = [
            'type' => 'resource',
            'soiree' => $soirees,
        ];

        $response->getBody()->write(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}