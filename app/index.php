<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set('America/Argentina/Buenos_Aires');

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once './db/AccesoDatos.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/JWTController.php';
require_once './controllers/CSVController.php';
require_once './controllers/ClienteController.php';
// require_once './middlewares/LoggerMiddleware.php'; // Ahora ya no lo necesitamos
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/ParamsMiddleware.php';

// Cargar ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

// Este era el funcionamiento del Login hasta el Sprint 2, sin usar JWT
/*
//La aplicación siempre va a necesitar estos parámetros mínimos, y logearse correctamente
$app
->add(new LoggerMiddleware) // Valido que exista el usuario en la base de datos
->add(new ParamsMiddleware(['sector', 'usuarioActual', 'claveActual'], true)); // Valido parametros esenciales
*/

// Levantar Servidor en puerto 666
// php -S localhost:666 -t app

// Rutas
// Usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')
    ->add(new ParamsMiddleware(['usuario', 'clave', 'rol', 'email', 'nombre', 'apellido']));

    $group->put('/{id}', \UsuarioController::class . ':ModificarUno')
    ->add(new ParamsMiddleware(['usuario', 'clave', 'rol', 'email', 'nombre', 'apellido', 'estado']));

    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
})
->add(new AuthMiddleware('Socio')); // Valido que sea del sector que yo quiero

// Productos
$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno')
    ->add(new ParamsMiddleware(['descripcion', 'tipo', 'seccion', 'precio', 'tiempoEstimado']));

    $group->put('/{id}', \ProductoController::class . ':ModificarUno')
    ->add(new ParamsMiddleware(['descripcion', 'tipo', 'seccion', 'precio', 'estado', 'tiempoEstimado']));
    
    $group->delete('/{id}', \ProductoController::class . ':BorrarUno');
})
->add(new AuthMiddleware('Socio')); // Valido que sea del sector que yo quiero

// Mesas
$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno')
    ->add(new ParamsMiddleware(['estado', 'codigo']));

    $group->put('/{id}', \MesaController::class . ':ModificarUno')
    ->add(new ParamsMiddleware(['estado', 'codigo']));

    $group->delete('/{id}', \MesaController::class . ':BorrarUno');
})
->add(new AuthMiddleware('Socio')); // Valido que sea del sector que yo quiero

// Pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{id}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':CargarUno')
    ->add(new ParamsMiddleware(['id_mesa', 'id_usuario', 'codigo', 'nombre_cliente', 'estado', 'producto']));

    $group->put('/{id}', \PedidoController::class . ':ModificarUno')
    ->add(new ParamsMiddleware(['id_mesa', 'id_usuario', 'codigo', 'nombre_cliente', 'estado']));

    $group->delete('/{id}', \PedidoController::class . ':BorrarUno');
})
->add(new AuthMiddleware('Socio', 'Mozo')); // Valido que sea del sector que yo quiero

// Acciones de Pedidos
$app->group('/accionesPedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':VerPendientes') // Muestra los productos pendientes 
    ->add(new AuthMiddleware('Socio', 'Mozo', 'Cocina', 'Barra', 'Cerveceria'));

    $group->post('/{proceso}', \PedidoController::class . ':ProcesarPreparacion')
    ->add(new AuthMiddleware('Cocina', 'Barra', 'Cerveceria'))
    ->add(new ParamsMiddleware(['idProducto', 'idPedido']));
});

// Acciones de Mesas
$app->group('/accionesMesas', function (RouteCollectorProxy $group) {
    $group->post('/asociarFoto', \MesaController::class . ':AsociarFoto')
    ->add(new AuthMiddleware('Mozo'))
    ->add(new ParamsMiddleware(['idMesa', 'idPedido']));

    $group->post('/cobrarMesa', \MesaController::class . ':CobrarMesa')
    ->add(new AuthMiddleware('Mozo'))
    ->add(new ParamsMiddleware(['idMesa']));

    $group->post('/cerrarMesa', \MesaController::class . ':CerrarMesa')
    ->add(new AuthMiddleware('Socio'))
    ->add(new ParamsMiddleware(['idMesa']));
});

// Acciones de Clientes
$app->group('/accionesClientes', function (RouteCollectorProxy $group) {
    $group->post('/consultarPedido', \ClienteController::class . ':ConsultarPedido')
    ->add(new AuthMiddleware('Cliente'))
    ->add(new ParamsMiddleware(['codigoPedido']));
});

// Autenticación
$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', \JWTController::class . ':SolicitarToken');
})
->add(new ParamsMiddleware(['usuario', 'clave', 'sector']));

// Cargar/Descargar CSV
$app->group('/csv', function (RouteCollectorProxy $group) {
    $group->post('/cargarUsuarios', \CSVController::class . ':CargarDatos');
    $group->get('/descargarUsuarios', \CSVController::class . ':DescargarDatos');
});
// ->add(new AuthMiddleware('Socio'));


$app->get('[/]', function (Request $request, Response $response) {
    $payload = json_encode(array("mensaje" => "Hola Mundo. Slim Framework 4 PHP"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
?>