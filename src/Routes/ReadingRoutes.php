<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/../Controllers/ReadingController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

function registerReadingRoutes(
    App $app,
    PDO $pdo
): void {

    /**
     * POST /books/{id}/read
     *
     * Enregistrer la lecture d'un livre
     */
    $app->post('/books/{id}/read', function (
        Request $request,
        Response $response,
        array $args
    ) use ($pdo): Response {

        $readingController =
            new ReadingController($pdo);

        return $readingController->store(
            $request,
            $response,
            (int) $args['id']
        );

    })->add(
        new AuthMiddleware($pdo)
    );

        $app->get('/readings', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $readingController =
            new ReadingController($pdo);

        return $readingController->index(
            $request,
            $response
        );

    })->add(
        new AuthMiddleware($pdo)
    );
}