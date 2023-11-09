<?php

class Mesa
{
    public $id;
    public $estado;
    public $codigo;
    public $foto;

    public function crearMesa()
    {
        $retorno = 'Error al obtener el ultimo ID insertado';

        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (estado, codigo, foto) VALUES (:estado, :codigo, :foto)");
            $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
            $consulta->bindValue(':foto', '-', PDO::PARAM_STR);
            $consulta->execute();

            $ultimoId = $objAccesoDatos->obtenerUltimoId();

            if ($ultimoId > 0)
            {
                $nombreFoto = $this->guardarImagen($ultimoId);
                $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET foto = :nombreFoto WHERE id = :id");
                $consulta->bindValue(':nombreFoto', $nombreFoto, PDO::PARAM_STR);
                $consulta->bindValue(':id', $ultimoId, PDO::PARAM_INT);
                $consulta->execute();

                $retorno = $ultimoId;
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado, codigo, foto FROM mesas WHERE estado != 'Eliminada'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerMesa($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado, codigo, foto FROM mesas WHERE id = :id AND estado != 'Eliminada'");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function modificarMesa($id, $estado, $codigo)
    {
        $retorno = 'Error al modificar Mesa';

        try {
            if (self::obtenerMesa($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado, codigo = :codigo WHERE id = :id");
        
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
                //$consulta->bindValue(':foto', $foto, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Mesa modificada con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro la mesa';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function borrarMesa($id)
    {
        $retorno = 'Error al eliminar Mesa';

        try {
            if (self::obtenerMesa($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado WHERE id = :id");

                $estado = 'Eliminada';
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Mesa eliminada con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro la mesa';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    private function guardarImagen($id) {
        $retorno = null;

        if (isset($_FILES['foto']))
        {
            $nombreImagen = 'mesas_' . $id . '.jpg';

            $destino = "img/ImagenesDeMesas/" . $nombreImagen;
            move_uploaded_file($_FILES['foto']['tmp_name'], $destino);

            $retorno = $nombreImagen;
        }

        return $retorno;
    }
}