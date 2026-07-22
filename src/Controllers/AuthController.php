<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    /**
     * Inscription d'un utilisateur
     */
    public function register(
        Request $request,
        Response $response
    ): Response {

        $data = $request->getParsedBody();

        if ($data === null || empty($data)) {
            $body = (string) $request->getBody();

            $data = json_decode($body, true);
        }

        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (
            empty($nom) ||
            empty($prenom) ||
            empty($email) ||
            empty($password)
        ) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Tous les champs sont obligatoires'
                ],
                400
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Adresse email invalide'
                ],
                400
            );
        }

        $existingUser = $this->userModel->findByEmail($email);

        if ($existingUser !== null) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Cet email est déjà utilisé'
                ],
                409
            );
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $this->userModel->create(
            $nom,
            $prenom,
            $email,
            $passwordHash
        );

        return $this->jsonResponse(
            $response,
            [
                'message' => 'Utilisateur créé avec succès'
            ],
            201
        );
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(
        Request $request,
        Response $response
    ): Response {

        $data = $request->getParsedBody();

        if ($data === null || empty($data)) {
            $body = (string) $request->getBody();

            $data = json_decode($body, true);
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (
            empty($email) ||
            empty($password)
        ) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Email et mot de passe obligatoires'
                ],
                400
            );
        }

        $user = $this->userModel->findByEmail($email);

        if ($user === null) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Email ou mot de passe incorrect'
                ],
                401
            );
        }

        if (
            !password_verify(
                $password,
                $user['password_hash']
            )
        ) {
            return $this->jsonResponse(
                $response,
                [
                    'message' => 'Email ou mot de passe incorrect'
                ],
                401
            );
        }

        // Générer un token sécurisé
        $token = bin2hex(
            random_bytes(32)
        );

        // Enregistrer le token dans la base de données
        $this->userModel->updateToken(
            (int) $user['id'],
            $token
        );

        // Retourner la réponse
        return $this->jsonResponse(
            $response,
            [
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]
        );
    }

    /**
     * Retourner une réponse JSON
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