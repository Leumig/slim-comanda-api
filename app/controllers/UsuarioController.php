<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $rol = $parametros['rol'];
        $email = $parametros['email'];
        $nombre = $parametros['nombre'];
        $apellido = $parametros['apellido'];

        // Hasheamos la contraseña
        $claveHasheada = password_hash($clave, PASSWORD_DEFAULT);

        // Creamos el usuario
        $usuarioNuevo = new Usuario();
        $usuarioNuevo->usuario = $usuario;
        $usuarioNuevo->clave = $claveHasheada;
        $usuarioNuevo->rol = $rol;
        $usuarioNuevo->email = $email;
        $usuarioNuevo->nombre = $nombre;
        $usuarioNuevo->apellido = $apellido;
        $usuarioNuevo->fecha_alta = date('Y-m-d H:i:s');

        $respuesta = $usuarioNuevo->crearUsuario();

        if (is_numeric($respuesta))
        {
            $payload = json_encode(array("mensaje" => "Usuario creado con exito, ID: " . $respuesta));
        } else {
            $payload = json_encode(array("error" => $respuesta));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por id
        $id = $args['id'];
        $usuario = Usuario::obtenerUsuario($id);
        $payload = $usuario !== false?json_encode($usuario):json_encode(array("error" => "No se encontro"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaDeUsuarios" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['id'];
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $rol = $parametros['rol'];
        $email = $parametros['email'];
        $nombre = $parametros['nombre'];
        $apellido = $parametros['apellido'];
        $estado = $parametros['estado'];
        $respuesta = Usuario::modificarUsuario($id, $usuario, $clave, $rol, $email, $nombre, $apellido, $estado);
        
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        $respuesta = Usuario::borrarUsuario($id);
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
