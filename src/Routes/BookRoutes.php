<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/../Controllers/BookController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

function registerBookRoutes(
    App $app,
    PDO $pdo
): void {

    /**
     * POST /books
     *
     * Ajouter un livre
     */
    $app->post('/books', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $bookController =
            new BookController($pdo);

        return $bookController->store(
            $request,
            $response
        );

    })->add(
        new AuthMiddleware($pdo)
    );


    /**
     * GET /books
     *
     * Afficher tous les livres
     */
    $app->get('/books', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $bookController =
            new BookController($pdo);

        return $bookController->index(
            $request,
            $response
        );

    })->add(
        new AuthMiddleware($pdo)
    );

    /**
     * GET /books/{id}
     *
     * Afficher un livre par son ID
     */
    $app->get('/books/{id}', function (
        Request $request,
        Response $response,
        array $args
    ) use ($pdo): Response {

        $bookController =
            new BookController($pdo);

        return $bookController->show(
            $request,
            $response,
            (int) $args['id']
        );

    })->add(
        new AuthMiddleware($pdo)
    );

    /**
     * PUT /books/{id}
     *
     * Modifier un livre
     */
    $app->put('/books/{id}', function (
        Request $request,
        Response $response,
        array $args
    ) use ($pdo): Response {

        $bookController =
            new BookController($pdo);

        return $bookController->update(
            $request,
            $response,
            (int) $args['id']
        );

    })->add(
        new AuthMiddleware($pdo)
    );

    /**
     * DELETE /books/{id}
     *
     * Supprimer un livre
     */
    $app->delete('/books/{id}', function (
        Request $request,
        Response $response,
        array $args
    ) use ($pdo): Response {

        $bookController =
            new BookController($pdo);

        return $bookController->destroy(
            $request,
            $response,
            (int) $args['id']
        );

    })->add(
        new AuthMiddleware($pdo)
    );
}