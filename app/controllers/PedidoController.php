<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id_mesa = $parametros['id_mesa'];
        $id_usuario = $parametros['id_usuario'];
        $codigo = $parametros['codigo'];
        $nombre_cliente = $parametros['nombre_cliente'];
        $estado = $parametros['estado'];
        $productos = $parametros['producto'];

        // Creamos el Pedido
        $pedido = new Pedido();
        $pedido->id_mesa = $id_mesa;
        $pedido->id_usuario = $id_usuario;
        $pedido->codigo = $codigo;
        $pedido->nombre_cliente = $nombre_cliente;
        $pedido->estado = $estado;
        
        $respuesta = $pedido->crearPedido($productos);

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        if (is_numeric($respuesta))
        {
            $payload = json_encode(array("mensaje" => "Pedido creado con exito, ID: " . $respuesta));

            foreach ($productos as $producto) {
                $idProducto = $producto['id_producto'];
                $cantidad = $producto['cantidad'];
                $this->AsociarProductoPedido($respuesta, $idProducto, $cantidad);
            }
        } else {
            $payload = json_encode(array("error" => $respuesta));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function AsociarProductoPedido($idPedido, $idProducto, $cantidad)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad) VALUES (:id_pedido, :id_producto, :cantidad)");

        $consulta->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->execute();
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Pedido por id
        $id = $args['id'];
        $pedido = Pedido::obtenerPedido($id);
        $payload = $pedido !== false?json_encode($pedido):json_encode(array("error" => "No se encontro"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaDePedidos" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['id'];
        $id_mesa = $parametros['id_mesa'];
        $id_usuario = $parametros['id_usuario'];
        $codigo = $parametros['codigo'];
        $nombre_cliente = $parametros['nombre_cliente'];
        $estado = $parametros['estado'];
        $respuesta = Pedido::modificarPedido($id, $id_mesa, $id_usuario, $codigo, $nombre_cliente, $estado);
        
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        $respuesta = Pedido::borrarPedido($id);
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
