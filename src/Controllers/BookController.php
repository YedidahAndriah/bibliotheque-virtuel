<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../Models/Book.php';

class BookController
{
    private Book $bookModel;

    public function __construct(PDO $pdo)
    {
        $this->bookModel = new Book($pdo);
    }
    
    /**
     * Ajouter un nouveau livre
     */
    public function store(
        Request $request,
        Response $response
    ): Response {

        $data = $request->getParsedBody();

        if ($data === null || empty($data)) {

            $body = (string) $request->getBody();

            $data = json_decode(
                $body,
                true
            );
        }

        $titre = trim(
            $data['titre'] ?? ''
        );

        $description =
            $data['description'] ?? null;

        $datePublication =
            $data['date_publication'] ?? null;

        $fichier =
            $data['fichier'] ?? null;

        // Récupérer l'identifiant de l'auteur
        $auteurId = (int) (
            $data['auteur_id'] ?? 0
        );


        if (empty($titre)) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Le titre est obligatoire'
                ],
                400
            );
        }


        if ($auteurId <= 0) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'L\'auteur est obligatoire'
                ],
                400
            );
        }


        $this->bookModel->create(
            $titre,
            $description,
            $datePublication,
            $fichier,
            $auteurId
        );


        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Livre créé avec succès'
            ],
            201
        );
    }

    /**
     * Afficher tous les livres
     */
    public function index(
        Request $request,
        Response $response
    ): Response {

        $books = $this->bookModel->getAll();

        return $this->jsonResponse(
            $response,
            [
                'books' => $books
            ]
        );
    }


    /**
     * Afficher un livre par son ID
     */
    public function show(
        Request $request,
        Response $response,
        int $id
    ): Response {

        $book = $this->bookModel->findById($id);

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

        return $this->jsonResponse(
            $response,
            [
                'book' => $book
            ]
        );
    }

    /**
     * Modifier un livre
     */
    public function update(
        Request $request,
        Response $response,
        int $id
    ): Response {

        $book = $this->bookModel->findById($id);

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

        $data = $request->getParsedBody();

        if ($data === null || empty($data)) {

            $body = (string) $request->getBody();

            $data = json_decode(
                $body,
                true
            );
        }

        $titre = trim(
            $data['titre'] ?? ''
        );

        $description =
            $data['description'] ?? null;

        $datePublication =
            $data['date_publication'] ?? null;

        $fichier =
            $data['fichier'] ?? null;

        if (empty($titre)) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Le titre est obligatoire'
                ],
                400
            );
        }

        $this->bookModel->update(
            $id,
            $titre,
            $description,
            $datePublication,
            $fichier
        );

        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Livre modifié avec succès'
            ]
        );
    }


    /**
     * Supprimer un livre
     */
    public function destroy(
        Request $request,
        Response $response,
        int $id
    ): Response {

        $book = $this->bookModel->findById($id);

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

        $this->bookModel->delete($id);

        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Livre supprimé avec succès'
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