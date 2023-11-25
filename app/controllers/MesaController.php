<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $estado = $parametros['estado'];
        $codigo = $parametros['codigo'];
        $foto = $_FILES['foto'];

        // Creamos la Mesa
        $mesa = new Mesa();
        $mesa->estado = $estado;
        $mesa->codigo = $codigo;
        $mesa->foto = $foto;

        $respuesta = $mesa->crearMesa();

        if (is_numeric($respuesta))
        {
            $payload = json_encode(array("mensaje" => "Mesa creada con exito, ID: " . $respuesta));
        } else {
            $payload = json_encode(array("error" => $respuesta));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos Mesa por id
        $id = $args['id'];
        $mesa = Mesa::obtenerMesa($id);
        $payload = $mesa !== false?json_encode($mesa):json_encode(array("error" => "No se encontro"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaDeMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $args['id'];
        $estado = $parametros['estado'];
        $codigo = $parametros['codigo'];
        //$foto = $_FILES['foto'];
        $respuesta = Mesa::modificarMesa($id, $estado, $codigo);
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        $respuesta = Mesa::borrarMesa($id);
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AsociarFoto($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idPedido = $parametros['idPedido'];
        $idMesa = $parametros['idMesa'];

        $mesa = Mesa::obtenerMesa($idMesa);
        $fotoMesa = 'N/A';

        if (is_a($mesa, 'Mesa')) {
            $fotoMesa = $mesa->foto;
        }

        $respuesta = 'No se logro asociar la foto de la mesa al pedido';

        try {
            $pedido = Pedido::obtenerPedido($idPedido);

            if (is_a($pedido, 'Pedido') && $pedido->id_mesa == $idMesa)
            {
                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET foto_mesa = :foto_mesa WHERE id = :id");
        
                $consulta->bindValue(':foto_mesa', $fotoMesa, PDO::PARAM_STR);
                $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
                $consulta->execute();
    
                $respuesta = 'Se asocio correctamente la foto de la mesa al pedido';
            } else {
                $respuesta = 'Ese pedido no existe o no tiene asociada esta mesa';
            }
        } catch (Exception $e) {
            $respuesta = 'Error: ' . $e->getMessage();
        }

        $payload = json_encode(array("mensaje" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CobrarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros['idMesa'];
        $mesa = Mesa::obtenerMesa($idMesa);
        $respuesta = 'No se logro cobrar la mesa';

        try {
            if (is_a($mesa, 'Mesa') && $mesa->estado == 'Con cliente comiendo') {
                $estadoNuevo = 'Con cliente pagando';

                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado WHERE id = :id");
        
                $consulta->bindValue(':estado', $estadoNuevo, PDO::PARAM_STR);
                $consulta->bindValue(':id', $idMesa, PDO::PARAM_INT);
                $consulta->execute();
    
                $respuesta = 'Se cobro la mesa correctamente';
            } else {
                $respuesta = 'Esa mesa no existe o no esta disponible para cobrarse';

            }
        } catch (Exception $e) {
            $respuesta = 'Error: ' . $e->getMessage();
        }

        $payload = json_encode(array("mensaje" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CerrarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros['idMesa'];
        $mesa = Mesa::obtenerMesa($idMesa);
        $respuesta = 'No se logro cerrar la mesa';

        try {
            if (is_a($mesa, 'Mesa') && $mesa->estado == 'Con cliente pagando') {
                $estadoNuevo = 'Cerrada';

                $objAccesoDato = AccesoDatos::obtenerInstancia();
                $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado, codigo_pedido = :codigo_pedido WHERE id = :id");
        
                $codigo = NULL;
                $consulta->bindValue(':estado', $estadoNuevo, PDO::PARAM_STR);
                $consulta->bindValue(':codigo_pedido', $codigo);
                $consulta->bindValue(':id', $idMesa, PDO::PARAM_INT);
                $consulta->execute();
    
                $respuesta = 'Se cerro la mesa correctamente';
            } else {
                $respuesta = 'Esa mesa no existe o no esta disponible para cerrarse';

            }
        } catch (Exception $e) {
            $respuesta = 'Error: ' . $e->getMessage();
        }

        $payload = json_encode(array("mensaje" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
