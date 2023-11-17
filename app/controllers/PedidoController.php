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
        // Tomo 'sector' del 'data' del payload del token reibido
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $sector = $data->sector;

        // Ahora ya no tomo 'sector' por parametros
        // $parametros = $request->getQueryParams();
        // $sector = $parametros['sector'];

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

    public function ProcesarPreparacion($request, $response, $args)
    {
        $mensaje = 'No se pudo poner nada en preparacion';

        // Tomo 'sector' del 'data' del payload del token reibido
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $sector = $data->sector;

        // Ahora ya no tomo 'sector' por parametros
        // $parametros = $request->getQueryParams();
        // $sector = $parametros['sector'];

        $parametros = $request->getParsedBody();
        $proceso = $args['proceso'];
        $idPedido = $parametros['idPedido'];
        $idProducto = $parametros['idProducto'];
        $tiempoPreparacion = $parametros['tiempoPreparacion'];

        $pedidoAPreparar = Pedido::obtenerPedido($idPedido);
        
        if ($proceso !== 'Iniciar' && $proceso !== 'Finalizar') {
            $mensaje = 'Ese procedimiento no existe, debe ser Iniciar o Finalizar';
            $payload = json_encode(array("mensaje" => $mensaje));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        $nuevoEstadoProducto = $proceso === 'Iniciar' ? 'En preparacion' : 'Listo para servir';

        if ($nuevoEstadoProducto === 'Listo para servir' && $pedidoAPreparar->estado === 'Pendiente') {
            $mensaje = 'Ese pedido no se puede finalizar porque todavia no esta en preparacion';
            $payload = json_encode(array("mensaje" => $mensaje));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        if ($pedidoAPreparar->estado !== 'Eliminado' && $pedidoAPreparar->estado !== 'Listo para servir') {
            $productosPosibles = Producto::obtenerProductosPorPedido($idPedido);

            foreach ($productosPosibles as $productoDisponible) { // Recorro los productos del pedido
                if ($productoDisponible->id == $idProducto) // Verifico que el producto esté en el pedido
                {
                    if ($productoDisponible->seccion === $sector) // Verifico que el producto sea del sector
                    {
                        // Tomo el 'estado' del prodocucto en la tabla relacionada
                        $estadoProductoDisp = $this->obtenerEstadoProductoDisponible($productoDisponible->id, $idPedido);

                        // Valido que el proceso sea posible
                        if ($nuevoEstadoProducto == $estadoProductoDisp) {
                            $mensaje = 'El producto ya estaba en ese estado de preparacion';
                            break;
                        } else if ($nuevoEstadoProducto === 'Listo para servir' && $estadoProductoDisp === 'Pendiente') {
                            $mensaje = 'No se puede finalizar el producto, ni siquiera estaba en preparacion';
                            break;
                        } else if ($nuevoEstadoProducto === 'En preparacion' && $estadoProductoDisp === 'Listo para servir') {
                            $mensaje = 'No se poner en preparacion un producto ya finalizado';
                            break;
                        }

                        // Cambio el estado del producto en la tabla relacionada y le asigno el tiempo
                        Producto::Preparar($pedidoAPreparar, $idProducto, $nuevoEstadoProducto, $tiempoPreparacion);

                        // Verifico en qué estado deberia setear al pedido en la tabla pedidos
                        $nuevoEstadoPedido = $this->VerificarFinalizacion($pedidoAPreparar);

                        // Cambio el estado del pedido en la tabla pedidos
                        Pedido::Preparar($pedidoAPreparar, $nuevoEstadoPedido);

                        $mensaje = 'Se realizo el procedimiento del producto correctamente';
                        break;
                    } else {
                        $mensaje = 'Ese producto no es de tu sector';
                        break;
                    }
                } else {
                    $mensaje = 'Ese producto no esta en ese pedido';
                }
            }
        } else {
            $mensaje = 'Ese pedido no esta disponible porque ya esta listo o esta eliminado';
        }

        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function obtenerEstadoProductoDisponible($idProducto, $idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT estado FROM pedidos_productos WHERE id_producto = :id_producto AND id_pedido = :id_pedido");

        $consulta->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    private function VerificarFinalizacion($pedido)
    {
        $listaEstados = [];

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT estado FROM pedidos_productos WHERE id_pedido = :id_pedido");

        $consulta->bindValue(':id_pedido', $pedido->id, PDO::PARAM_INT);
        $consulta->execute();

        $listaEstados = $consulta->fetchAll(PDO::FETCH_COLUMN); // FETCH_COLUMN para obtener una sola columna
        
        $enPreparacion = false;
        $finalizado = true;
        foreach ($listaEstados as $estado) {
            if ($estado === 'En preparacion' || $estado === 'Pendiente') {
                $enPreparacion = true;
                $finalizado = false;
            }
        }

        if ($finalizado) {
            $nuevoEstadoPedido = 'Listo para Servir';
        } else if ($enPreparacion) {
            $nuevoEstadoPedido = 'En preparacion';
        } else {
            $nuevoEstadoPedido = 'Pendiente';
        }
        
        return $nuevoEstadoPedido;
    }
}
