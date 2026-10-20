<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use nrv\application\ports\api\ServiceSpectacleInterface;

final class FiltrerParDateAction {

    public function __construct(
        private readonly ServiceSpectacleInterface $service
    ) {}

    public function __invoke(ServerRequestInterface $request,ResponseInterface $response,array $args
        ): ResponseInterface {
            $requestdate = $request->getQueryParams()['date'] ?? null;
            //$rquestStyleMusique = $request->getQueryParams()['style'] ?? null;
            try{
                $dtos = null;
                if($requestdate != null){
                    $date = new DateTime($requestdate);
                    $dtos = $this->service->filtrerParDate($date);
                }
                /*if($rquestStyleMusique != null){
                    $date = new DateTime();
                    $dtos = $this->service->filtrerParStyle($date);
                }*/
                

                return $this->json($response, $dtos, 200);
            }catch (\Throwable $e) {
                return $this->json($response, ['error' => 'Erreur interne du serveur'], 500);
            }
    }


    private function json(ResponseInterface $response, mixed $data, int $status): ResponseInterface
    {
        $response->getBody()->write((string) json_encode($data, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}