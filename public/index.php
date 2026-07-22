<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Controllers/AuthController.php';
require __DIR__ . '/../src/Routes/AuthRoutes.php';
require __DIR__ . '/../src/Controllers/UserController.php';
require __DIR__ . '/../src/Routes/UserRoutes.php';

// Charger .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Charger la configuration
$config = require __DIR__ . '/../config/database.php';

$dbConfig = $config['connections']['mysql'];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};" .
        "port={$dbConfig['port']};" .
        "dbname={$dbConfig['database']};" .
        "charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    die(
        "Erreur de connexion à la base de données : "
        . $e->getMessage()
    );
}

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$app->get('/test', function (
    Request $request,
    Response $response
): Response {
    $response->getBody()->write(
        'Slim + PDO fonctionnent'
    );

    return $response;
});

$app->get('/test-user', function (
    Request $request,
    Response $response
) use ($pdo): Response {

    $userModel = new User($pdo);

    $users = $userModel->getAll();

    $response->getBody()->write(
        json_encode(
            $users,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );

    return $response
        ->withHeader('Content-Type', 'application/json');
});

registerAuthRoutes($app, $pdo);
registerUserRoutes($app, $pdo);

$app->run();