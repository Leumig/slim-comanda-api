<?php

class ManejadorCSV
{
    public static function Cargar($contenido)
    {
        try {
            $respuesta = 'No se pudieron cargar los datos';
            self::reiniciarTabla();

            $filas = preg_split('/\r\n|\r|\n/', $contenido);

            array_shift($filas); // Borro el primer elemento del array (el encabezado)

            foreach ($filas as $fila) {
                echo "Fila: $fila\n";
            
                $fila = preg_replace('/"(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})""/', '$1', $fila);
                $fila = trim($fila, '"');
            
                $columnas = str_getcsv($fila, ',', '"', '\\');
            
                Usuario::crearPorCampos($columnas);
            }

            $respuesta = 'Se cargaron los datos y se actualizo la base de datos';
        } catch (Exception $e) {
            $respuesta = $e->getMessage();
        }

        return $respuesta;
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
