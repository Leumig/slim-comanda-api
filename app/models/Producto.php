<?php

class Producto
{
    public $id;
    public $descripcion;
    public $tipo;
    public $seccion;
    public $precio;
    public $estado;
    public $tiempo_estimado;

    public function crearProducto()
    {
        $retorno = 'Error al obtener el ultimo ID insertado';

        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (descripcion, tipo, seccion, precio, estado, tiempo_estimado) VALUES (:descripcion, :tipo, :seccion, :precio, :estado, :tiempo_estimado)");
    
            $estado = 'Activo';
            $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':seccion', $this->seccion, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $this->precio);
            $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
            $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado);
            $consulta->execute();

            $retorno = $objAccesoDatos->obtenerUltimoId();
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion, tipo, seccion, precio, estado, tiempo_estimado FROM productos WHERE estado != 'Eliminado'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion, tipo, seccion, precio, estado, tiempo_estimado FROM productos WHERE id = :id AND estado != 'Eliminado'");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($id, $descripcion, $tipo, $seccion, $precio, $estado, $tiempo_estimado)
    {
        $retorno = 'Error al modificar Producto';

        try {
            if (self::obtenerProducto($id))
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET descripcion = :descripcion, tipo = :tipo, seccion = :seccion, precio = :precio, estado = :estado, tiempo_estimado = :tiempo_estimado WHERE id = :id");
        
                $consulta->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
                $consulta->bindValue(':seccion', $seccion, PDO::PARAM_STR);
                $consulta->bindValue(':precio', $precio);
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':tiempo_estimado', $tiempo_estimado);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();

                $retorno = 'Producto modificado con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro al producto';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function borrarProducto($id)
    {
        $retorno = 'Error al eliminar Producto';

        try {
            if (self::obtenerProducto($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET estado = :estado WHERE id = :id");

                $estado = 'Eliminado';
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Producto eliminado con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro al producto';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function obtenerProductosPorPedido($idPedido)
    {
        $listaIDs = [];

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_producto FROM pedidos_productos WHERE id_pedido = :id_pedido");

        $consulta->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        $listaIDs = $consulta->fetchAll(PDO::FETCH_COLUMN); // FETCH_COLUMN para obtener una sola columna
        
        $listaProductos = self::obtenerProductosPorIDs($listaIDs);

        // Ahora $listaProductos contiene los objetos Producto asociados al pedido
        return $listaProductos;
    }

    private static function obtenerProductosPorIDs($listaIDs)
    {
        $listaProductos = [];

        foreach ($listaIDs as $idProducto) {
            $producto = Producto::obtenerProducto($idProducto);
            array_push($listaProductos, $producto);
        }

        return $listaProductos;
    }

    

    public static function Preparar($pedido, $idProducto, $nuevoEstadoProducto, $tiempoPreparacion)
    {
        // Cambiamos el estado del producto
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos_productos SET tiempo_preparacion = :tiempo_preparacion, estado = :estado, hora_fin = :hora_fin WHERE id_producto = :id_producto AND id_pedido = :id_pedido");

        if ($nuevoEstadoProducto === 'Listo para servir') {
            $horaFin = new DateTime('now');
            $horaFormateada = $horaFin->format('Y-m-d H:i:s');
        } else {
            $horaFormateada = NULL;
        }

        $tiempoPreparacion = DateTime::createFromFormat('H:i:s', $tiempoPreparacion);
        $tiempoFormateado = $tiempoPreparacion->format('H:i:s');

        $consulta->bindValue(':tiempo_preparacion', $tiempoFormateado);
        $consulta->bindValue(':estado', $nuevoEstadoProducto, PDO::PARAM_STR);
        $consulta->bindValue(':hora_fin', $horaFormateada, PDO::PARAM_STR);
        $consulta->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $pedido->id, PDO::PARAM_INT);
        $consulta->execute();
    }
}