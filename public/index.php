<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath('/bibliotheque-virtuel');

$app->get('/', function (Request $request, Response $response) {

    $html = '
        <h1>Bibliothèque virtuelle</h1>

        <nav>
            <a href="/bibliotheque-virtuel/">Accueil</a> |
            <a href="/bibliotheque-virtuel/test">Tester setBasePath</a>
        </nav>
    ';

    $response->getBody()->write($html);

    return $response;
});

$app->get('/test', function (Request $request, Response $response) {

    $response->getBody()->write("
        <h1>Page de test</h1>
        <p>Le setBasePath fonctionne correctement !</p>

        <a href='/bibliotheque-virtuel/'>
            Retour à l'accueil
        </a>
    ");

    return $response;
});

$app->run();