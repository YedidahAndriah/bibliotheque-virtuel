<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/../Controllers/AuthorController.php';

function registerAuthorRoutes(
    App $app,
    PDO $pdo
): void {

    /**
     * POST /authors/register
     *
     * Inscription d'un auteur
     */
    $app->post('/authors/register', function (
        Request $request,
        Response $response
    ) use ($pdo): Response {

        $authorController =
            new AuthorController($pdo);

        return $authorController->register(
            $request,
            $response
        );
    });
}