<?php
require_once './utils/AutentificadorJWT.php';

class JWTController
{
    public function SolicitarToken($request, $response, $args)
    {
        // Siempre vamos a recibir por POST
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $sector = $parametros['sector'];

        $datos = ['usuario' => $usuario, 'sector' => $sector];

        $respuesta = 'Tus credenciales no son validas';

        // Valido que exista coincidencia en la BD
        if ($this->validarCredenciales($usuario, $clave, $sector)) {
            $respuesta = AutentificadorJWT::CrearToken($datos);
        }

        $payload = json_encode(array("respuesta" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
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
