<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../Models/Reading.php';
require_once __DIR__ . '/../Models/Book.php';
require_once __DIR__ . '/../Models/Permission.php';

class ReadingController
{
    private Reading $readingModel;
    private Book $bookModel;
    private Permission $permissionModel;

    public function __construct(PDO $pdo)
    {
        $this->readingModel = new Reading($pdo);
        $this->bookModel = new Book($pdo);
        $this->permissionModel = new Permission($pdo);
    }

    /**
     * Lire un livre avec autorisation
     */
    public function store(
        Request $request,
        Response $response,
        int $livreId
    ): Response {

        // 1. Vérifier que le livre existe
        $book = $this->bookModel->findById(
            $livreId
        );

        if ($book === null) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Livre introuvable'
                ],
                404
            );
        }

        // 2. Récupérer l'utilisateur connecté
        $userId = $request->getAttribute(
            'user_id'
        );

        // 3. Vérifier son autorisation
        $hasPermission =
            $this->permissionModel->canRead(
                (int) $userId,
                $livreId
            );

        if (!$hasPermission) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Vous n\'avez pas le droit de lire ce livre'
                ],
                403
            );
        }

        // 4. Enregistrer la lecture
        $this->readingModel->create(
            $livreId,
            (int) $userId
        );

        // 5. Retourner le livre
        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Lecture autorisée',

                'book' => $book
            ]
        );
    }

    /**
     * Afficher l'historique de lecture
     * de l'utilisateur connecté
     */
    public function index(
        Request $request,
        Response $response
    ): Response {

        // Récupérer l'ID de l'utilisateur connecté
        $userId = $request->getAttribute(
            'user_id'
        );

        // Récupérer son historique de lecture
        $readings =
            $this->readingModel->getByUser(
                (int) $userId
            );

        return $this->jsonResponse(
            $response,
            [
                'readings' => $readings
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
                JSON_UNESCAPED_UNICODE
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