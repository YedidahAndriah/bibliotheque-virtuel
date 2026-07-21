<?php

namespace App\Controllers;

use App\Models\Book;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PDO;
use Exception;

class BookController
{
    private $book;

    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    /**
     * GET /books - Récupérer tous les livres avec pagination
     */
    public function listBooks(Request $request, Response $response)
    {
        try {
            $params = $request->getQueryParams();
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $books = $this->book->getAllBooks($limit, $offset);
            $total = $this->book->countBooks();

            $data = [
                'success' => true,
                'data' => $books,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ];

            return $this->jsonResponse($response, $data, 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /books/{id} - Récupérer un livre par son ID
     */
    public function getBook(Request $request, Response $response, $args)
    {
        try {
            $bookId = $args['id'] ?? null;

            if (!$bookId) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID du livre manquant',
                ], 400);
            }

            $book = $this->book->getBookById($bookId);

            if (!$book) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Livre non trouvé',
                ], 404);
            }

            // Récupérer les auteurs du livre
            $book['authors'] = $this->book->getBookAuthors($bookId);

            return $this->jsonResponse($response, [
                'success' => true,
                'data' => $book,
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /books - Créer un nouveau livre
     */
    public function createBook(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();

            // Validation des données requises
            if (empty($data['titre'])) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Le titre du livre est requis',
                ], 400);
            }

            $bookId = $this->book->createBook($data);

            // Si des auteurs sont fournis, les ajouter
            if (!empty($data['author_ids']) && is_array($data['author_ids'])) {
                foreach ($data['author_ids'] as $authorId) {
                    $this->book->addAuthorToBook($bookId, $authorId);
                }
            }

            $book = $this->book->getBookById($bookId);
            $book['authors'] = $this->book->getBookAuthors($bookId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Livre créé avec succès',
                'data' => $book,
            ], 201);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /books/{id} - Mettre à jour un livre
     */
    public function updateBook(Request $request, Response $response, $args)
    {
        try {
            $bookId = $args['id'] ?? null;

            if (!$bookId) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID du livre manquant',
                ], 400);
            }

            $book = $this->book->getBookById($bookId);

            if (!$book) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Livre non trouvé',
                ], 404);
            }

            $data = $request->getParsedBody();

            // Mettre à jour les champs du livre
            $this->book->updateBook($bookId, $data);

            // Gérer les auteurs si fournis
            if (isset($data['author_ids']) && is_array($data['author_ids'])) {
                // Récupérer les auteurs actuels
                $currentAuthors = $this->book->getBookAuthors($bookId);
                $currentAuthorIds = array_column($currentAuthors, 'id');
                $newAuthorIds = $data['author_ids'];

                // Supprimer les auteurs qui ne sont plus dans la liste
                foreach ($currentAuthorIds as $authorId) {
                    if (!in_array($authorId, $newAuthorIds)) {
                        $this->book->removeAuthorFromBook($bookId, $authorId);
                    }
                }

                // Ajouter les nouveaux auteurs
                foreach ($newAuthorIds as $authorId) {
                    if (!in_array($authorId, $currentAuthorIds)) {
                        $this->book->addAuthorToBook($bookId, $authorId);
                    }
                }
            }

            $updatedBook = $this->book->getBookById($bookId);
            $updatedBook['authors'] = $this->book->getBookAuthors($bookId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Livre mis à jour avec succès',
                'data' => $updatedBook,
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /books/{id} - Supprimer un livre
     */
    public function deleteBook(Request $request, Response $response, $args)
    {
        try {
            $bookId = $args['id'] ?? null;

            if (!$bookId) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID du livre manquant',
                ], 400);
            }

            $book = $this->book->getBookById($bookId);

            if (!$book) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Livre non trouvé',
                ], 404);
            }

            $this->book->deleteBook($bookId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Livre supprimé avec succès',
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /books/search - Rechercher des livres par titre
     */
    public function searchBooks(Request $request, Response $response)
    {
        try {
            $params = $request->getQueryParams();
            $title = $params['q'] ?? '';

            if (empty($title)) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Le paramètre de recherche "q" est requis',
                ], 400);
            }

            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $books = $this->book->searchByTitle($title, $limit, $offset);

            return $this->jsonResponse($response, [
                'success' => true,
                'data' => $books,
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /books/{id}/authors - Ajouter un auteur à un livre
     */
    public function addAuthor(Request $request, Response $response, $args)
    {
        try {
            $bookId = $args['id'] ?? null;

            if (!$bookId) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID du livre manquant',
                ], 400);
            }

            $data = $request->getParsedBody();

            if (empty($data['author_id'])) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'L\'ID de l\'auteur est requis',
                ], 400);
            }

            $this->book->addAuthorToBook($bookId, $data['author_id']);

            $authors = $this->book->getBookAuthors($bookId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Auteur ajouté avec succès',
                'data' => $authors,
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /books/{id}/authors/{authorId} - Supprimer un auteur d'un livre
     */
    public function removeAuthor(Request $request, Response $response, $args)
    {
        try {
            $bookId = $args['id'] ?? null;
            $authorId = $args['authorId'] ?? null;

            if (!$bookId || !$authorId) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'L\'ID du livre et de l\'auteur sont requis',
                ], 400);
            }

            $this->book->removeAuthorFromBook($bookId, $authorId);

            $authors = $this->book->getBookAuthors($bookId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Auteur supprimé avec succès',
                'data' => $authors,
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Méthode utilitaire pour retourner une réponse JSON
     */
    private function jsonResponse(Response $response, $data, $status = 200)
    {
        $response = $response->withStatus($status);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response;
    }
}
