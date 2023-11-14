<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ParamsMiddleware
{
    private $paramsEspecificos;
    private $sonEsenciales;

    public function __construct($paramsEspecificos, $sonEsenciales = false)
    {
        $this->paramsEspecificos = $paramsEspecificos; // Guardo los parametros especificos
        $this->sonEsenciales = $sonEsenciales; // Guardo 'true' o 'false'
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Si es 'GET' recibo en URL, y sino, en el Body
        if ($_SERVER["REQUEST_METHOD"] === 'GET' || $_SERVER["REQUEST_METHOD"] === 'DELETE') {
            $paramsRecibidos = $request->getQueryParams();
        } elseif ($_SERVER["REQUEST_METHOD"] === 'PUT') {
            parse_str($request->getBody()->getContents(), $paramsRecibidos); // Si es PUT, hay que parsear
        } else {
            if ($request->getHeaderLine('Content-Type') === 'application/json') {
                $data = $request->getBody()->getContents(); // Si es JSON (raw), hay que hacer el decode
                $paramsRecibidos = json_decode($data, true);
            } else {
                $paramsRecibidos = $request->getParsedBody();
            }
        }

        // Parametros minimos para toda la APP
        $paramsEsenciales = ['sector', 'usuarioActual', 'claveActual'];

        // Si no son esenciales, los parametros requeridos van a ser los params especificos
        $paramsRequeridos = $this->sonEsenciales ? $paramsEsenciales : $this->paramsEspecificos;

        $mensajeErrorEsenciales = 'Faltan parametros de Login';
        $mensajeErrorEspecificos = 'Faltan parametros para realizar esta solicitud';
        $mensajeError = $this->sonEsenciales ? $mensajeErrorEsenciales : $mensajeErrorEspecificos;

        // Verifico que los parametros recibidos coincidan con los requeridos
        foreach ($paramsRequeridos as $param) {
            if (!isset($paramsRecibidos[$param])) {
                $response = new Response();
                $payload = json_encode(array('mensaje' => $mensajeError));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        return $response = $handler->handle($request);
    }
}