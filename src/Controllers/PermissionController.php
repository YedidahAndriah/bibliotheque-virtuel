<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../Models/Permission.php';

class PermissionController
{
    private Permission $permissionModel;

    public function __construct(PDO $pdo)
    {
        $this->permissionModel = new Permission($pdo);
    }

    /**
     * Donner une autorisation de lecture
     */
    public function store(
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

        $userId = (int) (
            $data['utilisateur_id'] ?? 0
        );

        $livreId = (int) (
            $data['livre_id'] ?? 0
        );

        // Date de fin facultative
        $dateFin = $data['date_fin'] ?? null;

        if ($userId <= 0 || $livreId <= 0) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'utilisateur_id et livre_id sont obligatoires'
                ],
                400
            );
        }

        $success = $this->permissionModel->create(
            $userId,
            $livreId,
            $dateFin
        );

        if (!$success) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Impossible de créer l\'autorisation'
                ],
                500
            );
        }

        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Autorisation créée avec succès'
            ],
            201
        );
    }

    /**
     * Afficher toutes les autorisations
     */
    public function index(
        Request $request,
        Response $response
    ): Response {

        $permissions =
            $this->permissionModel->getAll();

        return $this->jsonResponse(
            $response,
            [
                'permissions' => $permissions
            ]
        );
    }

    /**
     * Supprimer une autorisation
     */
    public function destroy(
        Request $request,
        Response $response,
        int $id
    ): Response {

        $permission =
            $this->permissionModel->findById($id);

        if ($permission === null) {

            return $this->jsonResponse(
                $response,
                [
                    'message' =>
                        'Autorisation introuvable'
                ],
                404
            );
        }

        $this->permissionModel->delete($id);

        return $this->jsonResponse(
            $response,
            [
                'message' =>
                    'Autorisation supprimée avec succès'
            ]
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
