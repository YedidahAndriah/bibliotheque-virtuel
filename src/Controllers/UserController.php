<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../Models/User.php';

class UserController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    /**
     * Afficher tous les utilisateurs
     */
    public function index(
        Request $request,
        Response $response
    ): Response {

        $users = $this->userModel->getAll();

        return $this->jsonResponse(
            $response,
            [
                'users' => $users
            ]
        );
    }

    /**
     * Afficher un utilisateur par son ID
     */
    public function show(
        Request $request,
        Response $response,
        int $id
    ): Response {

        $user = $this->userModel->findById($id);

        if ($user === null) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Utilisateur introuvable'
                ],
                404
            );
        }

        return $this->jsonResponse(
            $response,
            [
                'user' => $user
            ]
        );
    }

    /**
     * Réponse JSON
     */
    private function jsonResponse(
        Response $response,
        array $data,
        int $status = 200
    ): Response {

        $response->getBody()->write(
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );

        return $response
            ->withHeader(
                'Content-Type',
                'application/json'
            )
            ->withStatus($status);
    }
}