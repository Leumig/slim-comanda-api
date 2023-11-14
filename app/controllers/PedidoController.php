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

    //// ACCIONES DE PEDIDOS ///////////////////////////////////////////////////////////////////////////
    public function VerPendientes($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $sector = $parametros['sector'];

        $lista = Pedido::obtenerTodos();

        if ($sector !== 'Socio') { // Si la consulta es de un usuario distinto a socio...
            $listaFiltrada = []; // Creo una nueva lista filtrada

            foreach ($lista as $pedido) { // Recorro la lista de todos los pedidos
                if ($pedido->estado === 'Pendiente') { // Si el pedido esta en estado pendiente...
                    $idPedido = $pedido->id; // Tomo la id del pedido pendiente
                    $productos = Producto::obtenerProductosPorPedido($idPedido); // Tomo sus productos

                    foreach ($productos as $producto) { // Recorro los productos del pedido pendiente
                        if ($producto->seccion === $sector){ // Si el producto corresponde al sector...
                            // Significa que este pedido puede ser iniciado por el usuario
                            // Por lo tanto lo agrego a la lista de pendientes
                            //array_push($listaFiltrada, $idPedido); // Lo agrego a la lista filtrada
                            //break;

                            //Esto sería para mostrar CADA PRODUCTO que le corresponde al empleado
                            array_push($listaFiltrada, $producto); // Lo agrego a la lista filtrada
                        }
                    }
                }
            }
        }

        if ($sector === 'Socio') {
            $payload = json_encode(array("pedidosPendientes" => $lista));
        } else {
            $payload = json_encode(array("pedidosPendientes" => $listaFiltrada));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function IniciarPreparacion($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $sector = $parametros['sector'];

        $lista = Pedido::obtenerTodos();
        $payload = 'No se puso ningun pedido en preparacion';

        if ($sector !== 'Socio') { // Si la consulta es de un usuario distinto a socio...
            foreach ($lista as $pedido) { // Recorro la lista de todos los pedidos
                if ($pedido->estado === 'Pendiente') { // Si el pedido esta en estado pendiente...
                    $estadoPedido = 'En preparacion';
                    $idPedido = $pedido->id;
                    $productos = Producto::obtenerProductosPorPedido($idPedido); // Tomo sus productos

                    foreach ($productos as $producto) { // Recorro los productos del pedido pendiente
                        if ($producto->seccion === $sector){ // Si el producto corresponde al sector...
                            // Significa que este pedido puede ser iniciado por el usuario
                            // Por lo tanto modifico el estado de su pedido

                            // Le cambio el estado a 'En preparacion'
                            $objAccesoDato = AccesoDatos::obtenerInstancia();
                            $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");

                            $consulta->bindValue(':estado', $estadoPedido, PDO::PARAM_STR);
                            $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
                            $consulta->execute();

                            $payload = 'Se pusieron en preparacion los pedidos';
                        }
                    }
                    
                }
            }
        }
        
        $payload = json_encode(array("mensaje" => $payload));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function FinalizarPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $sector = $parametros['sector'];

        $lista = Pedido::obtenerTodos();
        $payload = 'No se puso ningun pedido listo para servir';

        if ($sector !== 'Socio') { // Si la consulta es de un usuario distinto a socio...
            foreach ($lista as $pedido) { // Recorro la lista de todos los pedidos
                if ($pedido->estado === 'En preparacion') { // Si el pedido esta en estado pendiente...
                    $estadoPedido = 'Listo para servir';
                    $idPedido = $pedido->id;
                    $productos = Producto::obtenerProductosPorPedido($idPedido); // Tomo sus productos

                    foreach ($productos as $producto) { // Recorro los productos del pedido en prep.
                        if ($producto->seccion === $sector){ // Si el producto corresponde al sector...
                            // Significa que este pedido puede ser iniciado por el usuario
                            // Por lo tanto modifico el estado de su pedido

                            // Le cambio el estado a 'Listo para servir'
                            $objAccesoDato = AccesoDatos::obtenerInstancia();
                            $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");

                            $consulta->bindValue(':estado', $estadoPedido, PDO::PARAM_STR);
                            $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
                            $consulta->execute();

                            $payload = 'Se pusieron los pedidos listos para servir';
                        }
                    }
                    
                }
            }
        }
        
        $payload = json_encode(array("mensaje" => $payload));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
