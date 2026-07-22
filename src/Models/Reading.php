<?php

class Reading
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Enregistrer une lecture
     */
    public function create(
        int $livreId,
        int $utilisateurId
    ): bool {

        $sql = "
            INSERT INTO lecture
            (
                livre_id,
                utilisateur_id
            )
            VALUES
            (
                :livre_id,
                :utilisateur_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':livre_id' => $livreId,
            ':utilisateur_id' => $utilisateurId
        ]);
    }

    /**
     * Récupérer l'historique de lecture d'un utilisateur
     */
    public function getByUser(
        int $userId
    ): array {

        $sql = "
            SELECT
                lecture.id AS lecture_id,
                lecture.date_lecture,

                livre.id AS livre_id,
                livre.titre,
                livre.description,
                livre.date_publication,
                livre.fichier

            FROM lecture

            INNER JOIN livre
                ON livre.id = lecture.livre_id

            WHERE lecture.utilisateur_id = :user_id

            ORDER BY lecture.date_lecture DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}