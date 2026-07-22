<?php

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Controllers/AuthController.php';
require __DIR__ . '/../src/Routes/AuthRoutes.php';

require __DIR__ . '/../src/Controllers/UserController.php';
require __DIR__ . '/../src/Routes/UserRoutes.php';

require __DIR__ . '/../src/Controllers/BookController.php';
require __DIR__ . '/../src/Routes/BookRoutes.php';

require __DIR__ . '/../src/Models/Author.php';
require __DIR__ . '/../src/Controllers/AuthorController.php';
require __DIR__ . '/../src/Routes/AuthorRoutes.php';

require __DIR__ . '/../src/Controllers/ReadingController.php';
require __DIR__ . '/../src/Routes/ReadingRoutes.php';

require __DIR__ . '/../src/Controllers/PermissionController.php';
require __DIR__ . '/../src/Routes/PermissionRoutes.php';

// Charger .env
$dotenv = Dotenv::createImmutable(
    __DIR__ . '/../'
);

$dotenv->load();


// Charger la configuration
$config = require __DIR__ . '/../config/database.php';

$dbConfig = $config['connections']['mysql'];


// Connexion à la base de données
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
        "Erreur de connexion à la base de données : " .
        $e->getMessage()
    );
}


// Créer l'application Slim
$app = AppFactory::create();


// Middlewares Slim
$app->addRoutingMiddleware();

$app->addBodyParsingMiddleware();


// Route d'accueil
$app->get('/', function (
    $request,
    $response
) {

    $response->getBody()->write(
        json_encode([
            'message' =>
                'API Bibliothèque virtuelle'
        ])
    );

    return $response
        ->withHeader(
            'Content-Type',
            'application/json'
        );
});


// Enregistrer les routes
registerAuthRoutes(
    $app,
    $pdo
);

registerUserRoutes(
    $app,
    $pdo
);

registerBookRoutes(
    $app, 
    $pdo
);

registerAuthorRoutes(
    $app, 
    $pdo
);

registerReadingRoutes(
    $app, 
    $pdo
);

registerPermissionRoutes(
    $app, 
    $pdo
);

// Lancer l'application
$app->run();