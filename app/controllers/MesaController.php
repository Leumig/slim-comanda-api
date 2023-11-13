<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $estado = $parametros['estado'];
        $codigo = $parametros['codigo'];
        $foto = $_FILES['foto'];

        // Creamos la Mesa
        $mesa = new Mesa();
        $mesa->estado = $estado;
        $mesa->codigo = $codigo;
        $mesa->foto = $foto;

        $respuesta = $mesa->crearMesa();

        if (is_numeric($respuesta))
        {
            $payload = json_encode(array("mensaje" => "Mesa creada con exito, ID: " . $respuesta));
        } else {
            $payload = json_encode(array("error" => $respuesta));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Mesa por id
        $id = $args['id'];
        $mesa = Mesa::obtenerMesa($id);
        $payload = $mesa !== false?json_encode($mesa):json_encode(array("error" => "No se encontro"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaDeMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['id'];
        $estado = $parametros['estado'];
        $codigo = $parametros['codigo'];
        //$foto = $_FILES['foto'];
        $respuesta = Mesa::modificarMesa($id, $estado, $codigo);
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        $respuesta = Mesa::borrarMesa($id);
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
