<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use nrv\application\ports\api\ServiceSpectacleInterface;

final class FiltrerAction {

    public function __construct(
        private readonly ServiceSpectacleInterface $service
    ) {}

    public function __invoke(ServerRequestInterface $request,ResponseInterface $response,array $args
        ): ResponseInterface {
            $requestdate = $request->getQueryParams()['date'] ?? null;
            $requestStyleMusique = $request->getQueryParams()['style'] ?? null;
            try{
                $dtos = [];
                if($requestdate != null && $requestStyleMusique != null){
                    $date = new DateTime($requestdate);
                    $dtos = $this->service->filtrer($date, $requestStyleMusique);
                }elseif($requestdate != null){
                    $date = new DateTime($requestdate);
                    $dtos = $this->service->filtrerParDate($date);
                }elseif($requestStyleMusique != null){
                    $dtos = $this->service->filtrerParStyle($requestStyleMusique);
                }
                
                

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