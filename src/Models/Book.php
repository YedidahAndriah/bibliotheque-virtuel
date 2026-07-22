<?php

class Book
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

        /**
     * Ajouter un nouveau livre
     * et l'associer à un auteur
     */
    public function create(
        string $titre,
        ?string $description,
        ?string $datePublication,
        ?string $fichier,
        int $auteurId
    ): bool {

        try {

            // Démarrer une transaction
            $this->db->beginTransaction();

            // 1. Créer le livre
            $sql = "
                INSERT INTO livre
                (
                    titre,
                    description,
                    date_publication,
                    fichier
                )
                VALUES
                (
                    :titre,
                    :description,
                    :date_publication,
                    :fichier
                )
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':titre' => $titre,
                ':description' => $description,
                ':date_publication' => $datePublication,
                ':fichier' => $fichier
            ]);

            // Récupérer l'ID du livre créé
            $livreId = (int) $this->db->lastInsertId();

            // 2. Créer la relation livre-auteur
            $sqlRelation = "
                INSERT INTO livre_auteur
                (
                    livre_id,
                    auteur_id
                )
                VALUES
                (
                    :livre_id,
                    :auteur_id
                )
            ";

            $stmtRelation = $this->db->prepare(
                $sqlRelation
            );

            $stmtRelation->execute([
                ':livre_id' => $livreId,
                ':auteur_id' => $auteurId
            ]);

            // Valider les deux opérations
            $this->db->commit();

            return true;

        } catch (PDOException $e) {

            // Annuler si une erreur survient
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }


    /**
     * Récupérer tous les livres
     * avec leurs auteurs
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                livre.id,
                livre.titre,
                livre.description,
                livre.date_publication,
                livre.fichier,

                auteur.id AS auteur_id,
                auteur.nom AS auteur_nom,
                auteur.prenom AS auteur_prenom,
                auteur.email AS auteur_email

            FROM livre

            INNER JOIN livre_auteur
                ON livre.id = livre_auteur.livre_id

            INNER JOIN auteur
                ON auteur.id = livre_auteur.auteur_id

            ORDER BY livre.id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Rechercher un livre par son ID
     * avec son auteur
     */
    public function findById(
        int $id
    ): ?array {

        $sql = "
            SELECT
                livre.id,
                livre.titre,
                livre.description,
                livre.date_publication,
                livre.fichier,

                auteur.id AS auteur_id,
                auteur.nom AS auteur_nom,
                auteur.prenom AS auteur_prenom,
                auteur.email AS auteur_email

            FROM livre

            INNER JOIN livre_auteur
                ON livre.id = livre_auteur.livre_id

            INNER JOIN auteur
                ON auteur.id = livre_auteur.auteur_id

            WHERE livre.id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $book = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $book ?: null;
    }

        /**
     * Modifier un livre
     */
    public function update(
        int $id,
        string $titre,
        ?string $description,
        ?string $datePublication,
        ?string $fichier
    ): bool {

        $sql = "
            UPDATE livre
            SET
                titre = :titre,
                description = :description,
                date_publication = :date_publication,
                fichier = :fichier
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':titre' => $titre,
            ':description' => $description,
            ':date_publication' => $datePublication,
            ':fichier' => $fichier
        ]);
    }

    /**
     * Supprimer un livre
     */
    public function delete(
        int $id
    ): bool {

        $sql = "
            DELETE FROM livre
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}