<?php

namespace App\Routes;

use App\Controllers\BookController;
use Slim\Routing\RouteCollectorProxy;

class BookRoutes
{
    public static function register($app)
    {
        $app->group('/books', function (RouteCollectorProxy $group) {
            // Récupérer tous les livres
            $group->get('', [BookController::class, 'listBooks']);

            // Rechercher les livres
            $group->get('/search', [BookController::class, 'searchBooks']);

            // Créer un nouveau livre
            $group->post('', [BookController::class, 'createBook']);

            // Récupérer un livre par ID
            $group->get('/{id}', [BookController::class, 'getBook']);

            // Mettre à jour un livre
            $group->put('/{id}', [BookController::class, 'updateBook']);

            // Supprimer un livre
            $group->delete('/{id}', [BookController::class, 'deleteBook']);

            // Ajouter un auteur à un livre
            $group->post('/{id}/authors', [BookController::class, 'addAuthor']);

            // Supprimer un auteur d'un livre
            $group->delete('/{id}/authors/{authorId}', [BookController::class, 'removeAuthor']);
        });
    }
}
