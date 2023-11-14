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
        // Valido el verbo HTTP y tomo los parametros como corresponda
        switch ($_SERVER["REQUEST_METHOD"]) {
            case 'GET':
            case 'DELETE':
                $parametros = $request->getQueryParams();
                break;
            case 'PUT':
                parse_str($request->getBody()->getContents(), $parametros); // Si es PUT, hay que parsear
            case 'POST':
                if ($request->getHeaderLine('Content-Type') === 'application/json') {
                    $data = $request->getBody()->getContents(); // Si es JSON (raw), hay que hacer el decode
                    $parametros = json_decode($data, true);
                } else {
                    $parametros = $request->getParsedBody();
                }
            default:
                $response = new Response();
                $response->getBody()->write(json_encode(['mensaje' => 'Verbo HTTP no valido']));
                return $response->withHeader('Content-Type', 'application/json');
            break;
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
