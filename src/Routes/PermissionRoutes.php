<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/../Controllers/PermissionController.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

function registerPermissionRoutes(
    App $app,
    PDO $pdo
): void {

    /**
     * POST /permissions
     *
     * Donner une autorisation de lecture
     */
    $app->post('/permissions', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $permissionController =
            new PermissionController($pdo);

        return $permissionController->store(
            $request,
            $response
        );

    })->add(
        new AuthMiddleware($pdo)
    );


    /**
     * GET /permissions
     *
     * Afficher toutes les autorisations
     */
    $app->get('/permissions', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $permissionController =
            new PermissionController($pdo);

        return $permissionController->index(
            $request,
            $response
        );

    })->add(
        new AuthMiddleware($pdo)
    );


    /**
     * DELETE /permissions/{id}
     *
     * Supprimer une autorisation
     */
    $app->delete('/permissions/{id}', function (
        Request $request,
        Response $response,
        array $args
    ) use ($pdo): Response {

        $permissionController =
            new PermissionController($pdo);

        return $permissionController->destroy(
            $request,
            $response,
            (int) $args['id']
        );

    })->add(
        new AuthMiddleware($pdo)
    );
}