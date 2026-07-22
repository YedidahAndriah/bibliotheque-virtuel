<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private string $requiredRole;

    public function __construct(
        string $requiredRole
    ) {
        $this->requiredRole = $requiredRole;
    }

    public function __invoke(
        Request $request,
        RequestHandler $handler
    ): Response {

        // Récupérer l'utilisateur ajouté par AuthMiddleware
        $user = $request->getAttribute(
            'user'
        );

        // Vérifier que l'utilisateur existe
        if ($user === null) {
            return $this->forbiddenResponse(
                'Accès non autorisé'
            );
        }

        // Vérifier le rôle
        if (
            $user['role'] !== $this->requiredRole
        ) {
            return $this->forbiddenResponse(
                'Accès réservé aux administrateurs'
            );
        }

        // Autoriser l'accès
        return $handler->handle(
            $request
        );
    }

    private function forbiddenResponse(
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
            ->withStatus(403);
    }
}