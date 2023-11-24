<?php
require_once './utils/ManejadorCSV.php';

class CSVController
{
    public function CargarDatos($request, $response, $args)
    {
        $archivos = $request->getUploadedFiles();
        $archivo = $archivos['archivo'];
    
        if ($archivo->getError() === UPLOAD_ERR_OK) {
            $stream = $archivo->getStream();
            $contenido = stream_get_contents($stream->detach());

            $respuesta = ManejadorCSV::Cargar($contenido);
    
            $payload = json_encode(array("mensaje" => $respuesta));
            $response->getBody()->write($payload);
    
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $payload = json_encode(array("error" => "Error al cargar el archivo"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    
    

    public function DescargarDatos($request, $response, $args)
    {
        try {
            $usuarios = Usuario::obtenerTodos(true); // Le paso true para que incluya los eliminados
    
            $response = $response->withHeader('Content-Type', 'text/csv');
            $response = $response->withHeader('Content-Disposition', 'attachment; filename=usuarios.csv');
    
            $output = fopen('php://output', 'w');
        
            fputcsv($output, array('id', 'usuario', 'clave', 'rol', 'email', 'nombre', 'apellido', 'estado', 'fecha_alta', 'fecha_baja'));
        
            foreach ($usuarios as $usuario) {
                fputcsv($output, array(
                    $usuario->id,
                    $usuario->usuario,
                    $usuario->clave,
                    $usuario->rol,
                    $usuario->email,
                    $usuario->nombre,
                    $usuario->apellido,
                    $usuario->estado,
                    $usuario->fecha_alta,
                    $usuario->fecha_baja,
                ));
            }
        
            fclose($output);
        
            return $response;
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => 'Ocurrio un error con la descarga'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}