<?php

class Permission
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Accorder un droit de lecture
     */
    public function create(
        int $utilisateurId,
        int $livreId,
        ?string $dateFin = null
    ): bool {

        $sql = "
            INSERT INTO autorisation
            (
                utilisateur_id,
                livre_id,
                date_fin
            )
            VALUES
            (
                :utilisateur_id,
                :livre_id,
                :date_fin
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':utilisateur_id' => $utilisateurId,
            ':livre_id' => $livreId,
            ':date_fin' => $dateFin
        ]);
    }
    /**
     * Vérifier si un utilisateur
     * possède le droit de lire un livre
     */
    public function canRead(
        int $utilisateurId,
        int $livreId
    ): bool {

        $sql = "
            SELECT id
            FROM autorisation
            WHERE utilisateur_id = :utilisateur_id
            AND livre_id = :livre_id
            AND (
                date_fin IS NULL
                OR date_fin >= NOW()
            )
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':utilisateur_id' => $utilisateurId,
            ':livre_id' => $livreId
        ]);

        return $stmt->fetch() !== false;
    }

        /**
     * Récupérer toutes les autorisations
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                autorisation.id,
                autorisation.utilisateur_id,
                autorisation.livre_id,
                autorisation.date_debut,
                autorisation.date_fin,

                utilisateur.nom AS utilisateur_nom,
                utilisateur.prenom AS utilisateur_prenom,
                utilisateur.email AS utilisateur_email,

                livre.titre AS livre_titre

            FROM autorisation

            INNER JOIN utilisateur
                ON utilisateur.id =
                   autorisation.utilisateur_id

            INNER JOIN livre
                ON livre.id =
                   autorisation.livre_id

            ORDER BY autorisation.id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * Rechercher une autorisation par son ID
     */
    public function findById(
        int $id
    ): ?array {

        $sql = "
            SELECT
                id,
                utilisateur_id,
                livre_id,
                date_debut,
                date_fin
            FROM autorisation
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $permission = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $permission ?: null;
    }


    /**
     * Supprimer une autorisation
     */
    public function delete(
        int $id
    ): bool {

        $sql = "
            DELETE FROM autorisation
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}