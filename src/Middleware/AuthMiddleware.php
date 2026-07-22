<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

require_once __DIR__ . '/../Models/User.php';

class AuthMiddleware
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function __invoke(
        Request $request,
        RequestHandler $handler
    ): Response {

        // Récupérer le header Authorization
        $authHeader = $request->getHeaderLine(
            'Authorization'
        );

        // Vérifier le format Bearer TOKEN
        if (
            empty($authHeader) ||
            !str_starts_with(
                $authHeader,
                'Bearer '
            )
        ) {
            return $this->unauthorizedResponse(
                'Token manquant'
            );
        }

        // Extraire le token
        $token = trim(
            substr(
                $authHeader,
                7
            )
        );

        // Rechercher l'utilisateur
        $user = $this->userModel->findByToken(
            $token
        );

        if ($user === null) {
            return $this->unauthorizedResponse(
                'Token invalide'
            );
        }

        // Ajouter l'utilisateur à la requête
        $request = $request->withAttribute(
            'user',
            $user
        );

        // Continuer vers la route
        return $handler->handle(
            $request
        );
    }

    private function unauthorizedResponse(
        string $message
    ): Response {

        $response = new SlimResponse();

        $response->getBody()->write(
            json_encode(
                [
                    'message' => $message
                ],
                JSON_UNESCAPED_UNICODE
            )
        );

        return $response
            ->withHeader(
                'Content-Type',
                'application/json'
            )
            ->withStatus(401);
    }
}