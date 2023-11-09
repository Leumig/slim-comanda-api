<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $descripcion = $parametros['descripcion'];
        $tipo = $parametros['tipo'];
        $sector = $parametros['sector'];
        $tiempoEstimado = $parametros['tiempoEstimado'];
        $precio = $parametros['precio'];

        // Creamos el Producto
        $producto = new Producto();
        $producto->descripcion = $descripcion;
        $producto->tipo = $tipo;
        $producto->sector = $sector;
        $producto->tiempoEstimado = $tiempoEstimado;
        $producto->precio = $precio;

        $respuesta = $producto->crearProducto();

        if (is_numeric($respuesta))
        {
            $payload = json_encode(array("mensaje" => "Producto creado con exito, ID: " . $respuesta));
        } else {
            $payload = json_encode(array("error" => $respuesta));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Producto por id
        $id = $args['id'];
        $producto = Producto::obtenerProducto($id);
        $payload = $producto !== false?json_encode($producto):json_encode(array("error" => "No se encontro"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaDeProductos" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['id'];
        $descripcion = $parametros['descripcion'];
        $tipo = $parametros['tipo'];
        $sector = $parametros['sector'];
        $tiempoEstimado = $parametros['tiempoEstimado'];
        $precio = $parametros['precio'];
        $estado = $parametros['estado'];

        $respuesta = Producto::modificarProducto($id, $descripcion, $tipo, $sector, $tiempoEstimado, $precio, $estado);

        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        $respuesta = Producto::borrarProducto($id);

        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
