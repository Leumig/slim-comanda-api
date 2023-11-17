<?php
require_once './utils/ManejadorCSV.php';

class CSVController
{
    public function CargarDatos($request, $response, $args)
    {
        $respuesta = 'No se pudieron cargar los datos';

        try {
            $respuesta = ManejadorCSV::Leer();
        } catch (Exception $e) {
            $respuesta = $e->getMessage();
        }

        $payload = json_encode(array("respuesta" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GuardarDatos($request, $response, $args)
    {
        $respuesta = 'No se pudieron guardar los datos';

        try {
            $datos = Usuario::obtenerTodos();
            ManejadorCSV::Guardar($datos);
            $respuesta = 'Los datos se guardaron correctamente en usuarios.csv';
        } catch (Exception $e) {
            $respuesta = $e->getMessage();
        }

        $payload = json_encode(array("respuesta" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
