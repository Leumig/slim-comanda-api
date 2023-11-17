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
        try {
            // Intento guardar en '$header' lo que reciba del header Authorization
            $header = $request->getHeaderLine('Authorization');
            $response = new Response();

            if (strlen($header) > 0) { // Valido que a '$header' le haya llegado algo
                $token = trim(explode("Bearer", $header)[1]);
    
                $data = AutentificadorJWT::ObtenerData($token);

                $sector = $data->sector;
        
                if ($sector === $this->sectorRequerido || $sector === $this->sectorOpcional1 || $sector === $this->sectorOpcional2 || $sector === $this->sectorOpcional3 || $sector === $this->sectorOpcional4)
                {
                    AutentificadorJWT::VerificarToken($token);
                    $response = $handler->handle($request);
                } else {
                    $payload = json_encode(array('mensaje' => 'No tenes permiso para realizar esta accion'));
                    $response->getBody()->write($payload);
                }
            } else {
                $payload = json_encode(array('mensaje' => 'No se recibio el header Authorization'));
                $response->getBody()->write($payload);
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'Ocurrio un error con el TOKEN'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
