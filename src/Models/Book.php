<?php

namespace App\Models;

use PDO;

class Book
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupérer tous les livres
     */
    public function getAllBooks($limit = null, $offset = 0)
    {
        $query = 'SELECT * FROM livre ORDER BY id DESC';
        
        if ($limit) {
            $query .= ' LIMIT :limit OFFSET :offset';
        }

        $stmt = $this->pdo->prepare($query);
        
        if ($limit) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un livre par ID
     */
    public function getBookById($id)
    {
        $query = 'SELECT * FROM livre WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Créer un nouveau livre
     */
    public function createBook($data)
    {
        $query = 'INSERT INTO livre (titre, description, date_publication, fichier) 
                  VALUES (:titre, :description, :date_publication, :fichier)';
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':titre' => $data['titre'] ?? null,
            ':description' => $data['description'] ?? null,
            ':date_publication' => $data['date_publication'] ?? null,
            ':fichier' => $data['fichier'] ?? null,
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Mettre à jour un livre
     */
    public function updateBook($id, $data)
    {
        $fields = [];
        $values = [':id' => $id];

        foreach (['titre', 'description', 'date_publication', 'fichier'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $values[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = 'UPDATE livre SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($values);
    }

    /**
     * Supprimer un livre
     */
    public function deleteBook($id)
    {
        $query = 'DELETE FROM livre WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Rechercher les livres par titre
     */
    public function searchByTitle($title, $limit = 10, $offset = 0)
    {
        $query = 'SELECT * FROM livre WHERE titre LIKE :titre ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':titre' => '%' . $title . '%',
            ':limit' => (int)$limit,
            ':offset' => (int)$offset,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter le nombre total de livres
     */
    public function countBooks()
    {
        $query = 'SELECT COUNT(*) as total FROM livre';
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    /**
     * Associer un auteur à un livre
     */
    public function addAuthorToBook($bookId, $authorId)
    {
        $query = 'INSERT INTO livre_auteur (livre_id, auteur_id) VALUES (:livre_id, :auteur_id)';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            ':livre_id' => $bookId,
            ':auteur_id' => $authorId,
        ]);
    }

    /**
     * Récupérer les auteurs d'un livre
     */
    public function getBookAuthors($bookId)
    {
        $query = 'SELECT a.* FROM auteur a 
                  INNER JOIN livre_auteur la ON a.id = la.auteur_id 
                  WHERE la.livre_id = :livre_id';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':livre_id' => $bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer un auteur d'un livre
     */
    public function removeAuthorFromBook($bookId, $authorId)
    {
        $query = 'DELETE FROM livre_auteur WHERE livre_id = :livre_id AND auteur_id = :auteur_id';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            ':livre_id' => $bookId,
            ':auteur_id' => $authorId,
        ]);
    }
}
