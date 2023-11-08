<?php

class Pedido
{
    public $id;
    public $id_mesa;
    public $id_usuario;
    //public $codigo;
    public $estado;
    //public $tiempo_estimado;
    //public $hora_inicio;
    //public $hora_fin;
    public $nombre_cliente;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id_mesa, id_usuario, estado, nombre_cliente) VALUES (:id_mesa, :id_usuario, :estado, :nombre_cliente)");
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, id_usuario, estado, nombre_cliente FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, id_usuario, estado, nombre_cliente FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function modificarPedido($id, $id_mesa, $id_usuario, $estado, $nombre_cliente)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET id_mesa = :estado, foto = :foto, rol = :rol WHERE id = :id");

        $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_cliente', $nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarPedido($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        //$consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET fechaBaja = :fechaBaja WHERE id = :id");
        //$fecha = new DateTime(date("d-m-Y"));
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");
        $estado = 'eliminado';
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        //$consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
}