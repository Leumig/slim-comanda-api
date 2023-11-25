<?php

class MozoController
{
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

    public function ActualizarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idPedido = $parametros['idPedido'];
        $idMesa = $parametros['idMesa'];

        $mesa = Mesa::obtenerMesa($idMesa);


    }
}
