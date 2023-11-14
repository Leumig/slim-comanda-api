<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $sectorRequerido;

    public function __construct($sectorRequerido)
    {
        $this->sectorRequerido = $sectorRequerido;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        // Si es 'GET' recibo en URL, y sino, en el Body
        if ($_SERVER["REQUEST_METHOD"] === 'GET' || $_SERVER["REQUEST_METHOD"] === 'DELETE') {
            $parametros = $request->getQueryParams();
        } elseif ($_SERVER["REQUEST_METHOD"] === 'PUT') {
            parse_str($request->getBody()->getContents(), $parametros); // Si es PUT, hay que parsear
        } else {
            if ($request->getHeaderLine('Content-Type') === 'application/json') {
                $data = $request->getBody()->getContents(); // Si es JSON (raw), hay que hacer el decode
                $parametros = json_decode($data, true);
            } else {
                $parametros = $request->getParsedBody();
            }
        }
        
        $sector = $parametros['sector'];

        if ($sector === $this->sectorRequerido) {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'Necesitas ser ' . $this->sectorRequerido . ' para realizar esta solicitud'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
