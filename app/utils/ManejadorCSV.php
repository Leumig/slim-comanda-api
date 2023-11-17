<?php

class ManejadorCSV
{
    public static function Leer()
    {
        try {
            $ruta = './data/usuarios.csv';
            $respuesta = 'No se pudo cargar ningun dato';
            $datos = [];
            $archivo = fopen($ruta, 'r');
            $header = fgetcsv($archivo);

            self::reiniciarTabla();
            
            while (($datos = fgetcsv($archivo)) !== false)
            {
                if (count($datos) >= 6)
                {
                    $nuevoElemento = Usuario::crearPorCampos($datos);

                    if ($nuevoElemento !== null) {
                        $respuesta = 'Se cargaron los datos y se actualizo la base de datos';
                    }
                }
            }
        
            fclose($archivo);
        } catch (Exception $e) {
            throw $e;
        }

        return $respuesta;
    }

    public static function Guardar($datos)
    {
        try {
            $ruta = './data/usuarios.csv';
            $archivo = fopen($ruta, 'w');
        
            // Si el archivo esta vacio, le escribo el encabezado
            if (filesize($ruta) === 0) {
                $encabezado = ['id', 'usuario', 'clave', 'rol', 'email', 'nombre', 'apellido', 'estado'];
                fputcsv($archivo, $encabezado);
            }
            
            foreach ($datos as $usuario) {
                $datosArray = get_object_vars($usuario);
                fputcsv($archivo, $datosArray);
            }
        
            fclose($archivo);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function reiniciarTabla()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM usuarios");
        $consulta->execute();

        $consulta = $objAccesoDatos->prepararConsulta("ALTER TABLE usuarios auto_increment = 1;");
        $consulta->execute();
    }
}
