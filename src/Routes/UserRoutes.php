<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';

function registerUserRoutes(
    App $app,
    PDO $pdo
): void {

    /**
     * GET /users
     * Route protégée par AuthMiddleware
     */
    $app->get('/users', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $userController = new UserController($pdo);

        return $userController->index(
            $request,
            $response
        );

    })->add(
        new AuthMiddleware($pdo)
    );


    /**
     * GET /users/{id}
     */
    $app->get('/users/{id}', function (
        Request $request,
        Response $response,
        array $args
    ) use ($pdo): Response {

        $userController = new UserController($pdo);

        return $userController->show(
            $request,
            $response,
            (int) $args['id']
        );
    });


    /**
     * GET /admin-only
     * Route réservée aux administrateurs
     */
    $app->get('/admin-only', function (
        Request $request,
        Response $response
    ): Response {

        $response->getBody()->write(
            json_encode([
                'message' => 'Bienvenue administrateur'
            ])
        );

        return $response
            ->withHeader(
                'Content-Type',
                'application/json'
            );

    })->add(
        new RoleMiddleware('admin')
    )->add(
        new AuthMiddleware($pdo)
    );
}