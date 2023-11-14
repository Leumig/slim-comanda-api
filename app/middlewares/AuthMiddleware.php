<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $sectorRequerido;
    private $sectorOpcional1;
    private $sectorOpcional2;
    private $sectorOpcional3;
    private $sectorOpcional4;

    public function __construct($sectorRequerido, $sectorOpcional1 = null,
        $sectorOpcional2 = null, $sectorOpcional3 = null, $sectorOpcional4 = null)
    {
        $this->sectorRequerido = $sectorRequerido;
        $this->sectorOpcional1 = $sectorOpcional1;
        $this->sectorOpcional2 = $sectorOpcional2;
        $this->sectorOpcional3 = $sectorOpcional3;
        $this->sectorOpcional4 = $sectorOpcional4;
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

        if ($sector === $this->sectorRequerido || $sector === $this->sectorOpcional1 || $sector === $this->sectorOpcional2 || $sector === $this->sectorOpcional3 || $sector === $this->sectorOpcional4) {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No tenes permiso para realizar esta accion'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
