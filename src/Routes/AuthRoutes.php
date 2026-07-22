<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

function registerAuthRoutes(
    App $app,
    PDO $pdo
): void {

    $app->post('/auth/register', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $authController = new AuthController($pdo);

        return $authController->register(
            $request,
            $response
        );
    });

    $app->post('/auth/login', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $authController = new AuthController($pdo);

        return $authController->login(
            $request,
            $response
        );
    });
}