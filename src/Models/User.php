<?php

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function create(
        string $nom,
        string $prenom,
        string $email,
        string $passwordHash,
        string $role = 'utilisateur'
    ): bool {
        $sql = "
            INSERT INTO utilisateur
            (nom, prenom, email, password_hash, role)
            VALUES
            (:nom, :prenom, :email, :password_hash, :role)
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':role' => $role
        ]);
    }

    /**
     * Rechercher un utilisateur par email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT *
            FROM utilisateur
            WHERE email = :email
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Rechercher un utilisateur par ID
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT id, nom, prenom, email, role
            FROM utilisateur
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Récupérer tous les utilisateurs
     */
    public function getAll(): array
    {
        $sql = "
            SELECT id, nom, prenom, email, role
            FROM utilisateur
            ORDER BY id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enregistrer un token pour un utilisateur
     */
    public function updateToken(
        int $userId,
        string $token
    ): bool {

        $sql = "
            UPDATE utilisateur
            SET token = :token
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':token' => $token,
            ':id' => $userId
        ]);
    }

    /**
     * Rechercher un utilisateur par son token
     */
    public function findByToken(
        string $token
    ): ?array {

        $sql = "
            SELECT id, nom, prenom, email, role
            FROM utilisateur
            WHERE token = :token
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':token' => $token
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}