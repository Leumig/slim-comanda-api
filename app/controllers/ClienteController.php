<?php

class ClienteController
{
    public function ConsultarPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigoPedido = $parametros['codigoPedido'];

        $respuesta = 'No se encontro el pedido';
        $pedido = null;

        try {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("SELECT id, id_mesa, id_usuario, codigo, nombre_cliente, estado, monto, tiempo_estimado, hora_inicio, hora_fin FROM pedidos WHERE codigo = :codigo AND estado != 'Eliminado'");
    
            $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
            $consulta->execute();
            $pedido = $consulta->fetchObject('Pedido');
        } catch (Exception $e) {
            $respuesta = 'Error: ' . $e->getMessage();
        }

        if ($pedido !== null) {
            $respuesta = $pedido;
        }

        $payload = json_encode(array("mensaje" => $respuesta));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
