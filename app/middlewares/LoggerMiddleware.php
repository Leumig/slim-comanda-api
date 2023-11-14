<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMiddleware
{
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

        $usuario = $parametros['usuarioActual'];
        $clave = $parametros['claveActual'];
        $sector = $parametros['sector'];

        // Valido que exista coincidencia en la BD
        if (!$this->validarCredenciales($usuario, $clave, $sector)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['mensaje' => 'Tus credenciales no son validas']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }

    // Esta función valida que las credenciales coincidan en la BD y además que el usuario no este eliminado
    private function validarCredenciales($usuarioRecibido, $claveRecibida, $sectorRecibido)
    {
        $retorno = false;
        $lista = Usuario::obtenerTodos();

        foreach ($lista as $usuario) {
            if ($usuarioRecibido === $usuario->usuario && $claveRecibida === $usuario->clave &&
                $sectorRecibido === $usuario->rol && $usuario->estado !== 'Eliminado') {
                $retorno = true;
            }
        }

        return $retorno;
    }
}
