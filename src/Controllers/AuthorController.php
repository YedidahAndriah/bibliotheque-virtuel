<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../Models/Author.php';

class AuthorController
{
    private Author $authorModel;

    public function __construct(PDO $pdo)
    {
        $this->authorModel = new Author($pdo);
    }

    /**
     * Inscription d'un auteur
     */
    public function register(
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

        $nom = trim(
            $data['nom'] ?? ''
        );

        $prenom = trim(
            $data['prenom'] ?? ''
        );

        $email = trim(
            $data['email'] ?? ''
        );

        $password =
            $data['password'] ?? '';

        if (
            empty($nom) ||
            empty($prenom) ||
            empty($email) ||
            empty($password)
        ) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Tous les champs sont obligatoires'
                ],
                400
            );
        }

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Adresse email invalide'
                ],
                400
            );
        }

        $existingAuthor =
            $this->authorModel->findByEmail(
                $email
            );

        if (
            $existingAuthor !== null
        ) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Cet email est déjà utilisé'
                ],
                409
            );
        }

        $passwordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $this->authorModel->create(
            $nom,
            $prenom,
            $email,
            $passwordHash
        );

        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Auteur créé avec succès'
            ],
            201
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