<?php

class Producto
{
    public $id;
    public $descripcion;
    public $tipo;
    public $seccion;
    public $tiempoEstimado;
    public $precio;
    public $estado;

    public function crearProducto()
    {
        $retorno = 'Error al obtener el ultimo ID insertado';

        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (descripcion, tipo, seccion, tiempoEstimado, precio, estado) VALUES (:descripcion, :tipo, :seccion, :tiempoEstimado, :precio, :estado)");
    
            $estado = 'Activo';
            $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':seccion', $this->seccion, PDO::PARAM_STR);
            $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $this->precio);
            $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion, tipo, seccion, tiempoEstimado, precio, estado FROM productos WHERE estado != 'Eliminado'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion, tipo, seccion, tiempoEstimado, precio, estado FROM productos WHERE id = :id AND estado != 'Eliminado'");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($id, $descripcion, $tipo, $seccion, $tiempoEstimado, $precio, $estado)
    {
        $retorno = 'Error al modificar Producto';

        try {
            if (self::obtenerProducto($id))
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET descripcion = :descripcion, tipo = :tipo, seccion = :seccion, tiempoEstimado = :tiempoEstimado, precio = :precio, estado = :estado WHERE id = :id");
        
                $consulta->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
                $consulta->bindValue(':seccion', $seccion, PDO::PARAM_STR);
                $consulta->bindValue(':tiempoEstimado', $tiempoEstimado, PDO::PARAM_STR);
                $consulta->bindValue(':precio', $precio);
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
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
}