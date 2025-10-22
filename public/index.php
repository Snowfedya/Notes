<?php

use App\Controllers\AuthController;
use App\Middleware\SessionMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add routing middleware
$app->addRoutingMiddleware();

// Add error handling middleware
$app->addErrorMiddleware(true, true, true);

// Database initialization
require_once __DIR__ . '/../config/database_init.php';

// Database connection
$pdo = require __DIR__ . '/../config/database.php';

// Add SessionMiddleware
$app->add(new SessionMiddleware($pdo));

// Health-check route
$app->get('/api/status', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(['success' => true, 'status' => 'OK']));
    return $response->withHeader('Content-Type', 'application/json');
});

use App\Controllers\NoteController;

// Auth routes
$app->group('/api/auth', function (RouteCollectorProxy $group) use ($pdo) {
    $authController = new AuthController($pdo);
    $group->post('/register', [$authController, 'register']);
    $group->post('/login', [$authController, 'login']);
    $group->post('/logout', [$authController, 'logout']);
    $group->get('/session', [$authController, 'getSession']);
});

// Notes routes
$app->group('/api/notes', function (RouteCollectorProxy $group) use ($pdo) {
    $noteController = new NoteController($pdo);
    $group->post('', [$noteController, 'createNote']);
    $group->get('', [$noteController, 'getAllNotes']);
    $group->get('/{id}', [$noteController, 'getNote']);
    $group->put('/{id}', [$noteController, 'updateNote']);
    $group->delete('/{id}', [$noteController, 'deleteNote']);
});

$app->run();
