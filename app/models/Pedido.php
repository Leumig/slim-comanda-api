<?php

class Pedido
{
    public $id;
    public $id_mesa;
    public $id_usuario;
    public $codigo;
    public $nombre_cliente;
    public $estado;
    public $monto;
    public $tiempo_estimado;
    public $hora_inicio;
    public $hora_fin;

    public function crearPedido($productos)
    {
        $retorno = 'Error al obtener el ultimo ID insertado';

        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_mesa, id_usuario, codigo, nombre_cliente, estado, monto, tiempo_estimado, hora_inicio) VALUES (:id_mesa, :id_usuario, :codigo, :nombre_cliente, :estado, :monto, :tiempo_estimado, NOW())");
    
            $monto = $this->calcularMonto($productos);
            $tiempoEstimado = $this->calcularTiempoEstimado($productos);

            $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
            $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
            $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
            $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':monto', $monto);
            $consulta->bindValue(':tiempo_estimado', $tiempoEstimado);
            $consulta->execute();
    
            $retorno = $objAccesoDatos->obtenerUltimoId();
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consultaXD: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_mesa, id_usuario, codigo, nombre_cliente, estado, monto, tiempo_estimado, hora_inicio, hora_fin FROM pedidos WHERE estado != 'Eliminado'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_mesa, id_usuario, codigo, nombre_cliente, estado, monto, tiempo_estimado, hora_inicio, hora_fin FROM pedidos WHERE id = :id AND estado != 'Eliminado'");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function modificarPedido($id, $id_mesa, $id_usuario, $codigo, $nombre_cliente, $estado)
    {
        $retorno = 'Error al modificar Pedido';

        try {
            if (self::obtenerPedido($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET id_mesa = :id_mesa, id_usuario = :id_usuario, codigo = :codigo, nombre_cliente = :nombre_cliente, estado = :estado WHERE id = :id");
        
                $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
                $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
                $consulta->bindValue(':nombre_cliente', $nombre_cliente, PDO::PARAM_STR);
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Pedido modificado con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro al pedido';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function borrarPedido($id)
    {
        $retorno = 'Error al eliminar Pedido';

        try {
            if (self::obtenerPedido($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");
        
                $estado = 'Eliminado';
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Pedido eliminado con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro al pedido';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    private function calcularMonto($productos)
    {
        $montoTotal = 0;

        foreach ($productos as $producto) {
            $precio = $this->obtenerPrecioProducto($producto['id_producto']);
            $cantidad = $producto['cantidad'];

            $montoActual = $precio * $cantidad;
            $montoTotal += $montoActual;
        }
    
        return $montoTotal;
    }

    private function obtenerPrecioProducto($id)
    {
        $producto = Producto::obtenerProducto($id);
        return $producto->precio;
    }

    
    private function calcularTiempoEstimado($productos)
    {
        $tiempoEstimado = '00:00:00';

        foreach ($productos as $producto) {
            $tiempo = $this->obtenerTiempoProducto($producto['id_producto']);

            if ($this->compararTiempos($tiempo, $tiempoEstimado) > 0) {
                $tiempoEstimado = $tiempo;
            }
        }

        return $tiempoEstimado;
    }

    private function obtenerTiempoProducto($id)
    {
        $producto = Producto::obtenerProducto($id);
        return $producto->tiempo_estimado;
    }
    
    
    private static function compararTiempos($tiempo1, $tiempo2)
    {
        return strcmp($tiempo1, $tiempo2);
    }
    
    public static function Preparar($pedido, $nuevoEstadoPedido)
    {
        // Cambiamos el estado del pedido
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado, hora_fin = :hora_fin WHERE id = :id");

        if ($nuevoEstadoPedido == 'Listo para servir') {
            $horaFin = new DateTime('now');
            $horaFinString = $horaFin->format('Y-m-d H:i:s');
            $nuevoEstadoMesa = 'Con cliente comiendo';
            Mesa::ActualizarMesa($nuevoEstadoMesa, $pedido);
        } else {
            $horaFinString = NULL;
        }

        $consulta->bindValue(':estado', $nuevoEstadoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':hora_fin', $horaFinString);
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerPedidoPorCodigo($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_mesa, id_usuario, codigo, nombre_cliente, estado, monto, tiempo_estimado, hora_inicio, hora_fin FROM pedidos WHERE codigo = :codigo AND estado != 'Eliminado'");
        $consulta->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }
}