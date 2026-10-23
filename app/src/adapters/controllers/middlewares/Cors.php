<?php

declare(strict_types=1);

namespace nrv\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

// N'autorise que les origines de la liste : un site tiers ne peut pas lire les réponses de l'API.
class Cors implements MiddlewareInterface
{
    private array $originesAutorisees;

    public function __construct(array $originesAutorisees)
    {
        $this->originesAutorisees = $originesAutorisees;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);
        $origine = $request->getHeaderLine('Origin');

        if (!in_array($origine, $this->originesAutorisees, true)) {
            return $response;
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', $origine)
            ->withHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
