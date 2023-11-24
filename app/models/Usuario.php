<?php

class Usuario
{
    public $id;
    public $usuario;
    public $clave;
    public $rol;
    public $email;
    public $nombre;
    public $apellido;
    public $estado;
    public $fecha_alta;
    public $fecha_baja;

    public function crearUsuario($fechasPreestablecidas = false)
    {
        $retorno = 'Error al obtener el ultimo ID insertado';
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();

            if (!$fechasPreestablecidas)
            {
                $consulta = $objAccesoDatos->prepararConsulta("
                    INSERT INTO usuarios (usuario, clave, rol, email, nombre, apellido, estado, fecha_alta)
                    VALUES (:usuario, :clave, :rol, :email, :nombre, :apellido, :estado, NOW())");
    
                $estado = 'Activo';
                $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
                $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
                $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
                $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
                $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
                $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
            } else {
                $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, clave, rol, email, nombre, apellido, estado, fecha_alta, fecha_baja) VALUES (:usuario, :clave, :rol, :email, :nombre, :apellido, :estado, :fecha_alta, :fecha_baja)");

                $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
                $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
                $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
                $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
                $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
                $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
                $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
                $consulta->bindValue(':fecha_alta', $this->fecha_alta);
                $consulta->bindValue(':fecha_baja', $this->fecha_baja);
            }

            $consulta->execute();
            $retorno = $objAccesoDatos->obtenerUltimoId();
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
            //echo $retorno;  // Agrega esta línea para imprimir el error
        }

        return $retorno;
    }

    public static function obtenerTodos($incluirEliminados = false)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        if (!$incluirEliminados)
        {
            $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, rol, email, nombre, apellido, estado, fecha_alta FROM usuarios WHERE estado != 'Eliminado'");
        } else {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, rol, email, nombre, apellido, estado, fecha_alta, fecha_baja FROM usuarios");
        }
        
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, rol, email, nombre, apellido, estado FROM usuarios WHERE id = :id AND estado != 'Eliminado'");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function modificarUsuario($id, $usuario, $clave, $rol, $email, $nombre, $apellido, $estado)
    {
        $retorno = 'Error al modificar Usuario';

        try {
            if (self::obtenerUsuario($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET usuario = :usuario, clave = :clave, rol = :rol, email = :email, nombre = :nombre, apellido = :apellido, estado = :estado WHERE id = :id");
                
                $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
                $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);
                $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
                $consulta->bindValue(':email', $email, PDO::PARAM_STR);
                $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
                $consulta->bindValue(':apellido', $apellido, PDO::PARAM_STR);
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Usuario modificado con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro al usuario';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function borrarUsuario($id)
    {
        $retorno = 'Error al eliminar Usuario';

        try {
            if (self::obtenerUsuario($id) !== false)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET estado = :estado, fecha_baja = NOW() WHERE id = :id AND estado != 'Eliminado'");

                $estado = 'Eliminado';
                $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();
                $retorno = 'Usuario eliminado con exito, ID: ' . $id;
            } else {
                $retorno = 'No se encontro al usuario';
            }
        } catch (PDOException $e) {
            $retorno = 'Error al ejecutar la consulta: ' . $e->getMessage();
        }

        return $retorno;
    }

    public static function crearPorCampos($datos)
    {
        $id = isset($datos[0]) ? $datos[0] : null;
        $usuario = isset($datos[1]) ? $datos[1] : null;
        $clave = isset($datos[2]) ? $datos[2] : null;
        $rol = isset($datos[3]) ? $datos[3] : null;
        $email = isset($datos[4]) ? $datos[4] : null;
        $nombre = isset($datos[5]) ? $datos[5] : null;
        $apellido = isset($datos[6]) ? $datos[6] : null;
        $estado = isset($datos[7]) ? $datos[7] : null;
        $fecha_alta = isset($datos[8]) ? $datos[8] : null;
        $fecha_baja = isset($datos[9]) ? $datos[9] : null;
    
        if ($clave !== null)
        {
            $usuarioNuevo = new Usuario();
            $usuarioNuevo->id = $id;
            $usuarioNuevo->usuario = $usuario;
            $usuarioNuevo->clave = $clave;
            $usuarioNuevo->rol = $rol;
            $usuarioNuevo->email = $email;
            $usuarioNuevo->nombre = $nombre;
            $usuarioNuevo->apellido = $apellido;
            $usuarioNuevo->estado = $estado;
            $usuarioNuevo->fecha_alta = ($fecha_alta) ? $fecha_alta : null;
            $usuarioNuevo->fecha_baja = ($fecha_baja) ? $fecha_baja : null;

            $usuarioNuevo->crearUsuario(true);
        }
    }
}