<?php

class Mesa
{
    public $id;
    public $estado;
    public $foto;

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (estado, foto) VALUES (:estado, :foto)");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);

        $idActual = $objAccesoDatos->obtenerUltimoId();
        $foto = $this->guardarImagen($idActual);

        $consulta->bindValue(':foto', $foto, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado, foto FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerMesa($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado, foto FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function modificarMesa($id, $estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado WHERE id = :id");

        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        //$consulta->bindValue(':foto', $foto, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarMesa($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET fechaBaja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    private function guardarImagen($id) {
        $retorno = null;

        if (isset($_FILES['foto']))
        {
            $nombreImagen = 'mesas_' . $id . '.jpg';
            // $this->foto = $nombreImagen;

            $destino = "img/ImagenesDeMesas/" . $nombreImagen;
            move_uploaded_file($_FILES['foto']['tmp_name'], $destino);

            $retorno = $nombreImagen;
        }

        return $retorno;
    }
}