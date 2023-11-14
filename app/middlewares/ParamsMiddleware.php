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
        // Valido el verbo HTTP y tomo los parametros como corresponda
        switch ($_SERVER["REQUEST_METHOD"]) {
            case 'GET':
            case 'DELETE':
                $paramsRecibidos = $request->getQueryParams();
                break;
            case 'PUT':
                parse_str($request->getBody()->getContents(), $paramsRecibidos); // Si es PUT, hay que parsear
            case 'POST':
                if ($request->getHeaderLine('Content-Type') === 'application/json') {
                    $data = $request->getBody()->getContents(); // Si es JSON (raw), hay que hacer el decode
                    $paramsRecibidos = json_decode($data, true);
                } else {
                    $paramsRecibidos = $request->getParsedBody();
                }
            default:
                $response = new Response();
                $response->getBody()->write(json_encode(['mensaje' => 'Verbo HTTP no valido']));
                return $response->withHeader('Content-Type', 'application/json');
            break;
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