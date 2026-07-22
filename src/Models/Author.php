<?php

class Author
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Créer un auteur
     */
    public function create(
        string $nom,
        string $prenom,
        string $email,
        string $passwordHash
    ): bool {

        $sql = "
            INSERT INTO auteur
            (
                nom,
                prenom,
                email,
                password_hash
            )
            VALUES
            (
                :nom,
                :prenom,
                :email,
                :password_hash
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);
    }

    /**
     * Rechercher un auteur par email
     */
    public function findByEmail(
        string $email
    ): ?array {

        $sql = "
            SELECT *
            FROM auteur
            WHERE email = :email
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $author = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $author ?: null;
    }
}