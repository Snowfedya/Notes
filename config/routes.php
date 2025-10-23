<?php

use App\Services\Router;

$router = new Router();

// Authentication
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/logout', 'AuthController@logout');

// Notes
$router->get('/api/notes', 'NoteController@index');
$router->get('/api/notes/{id}', 'NoteController@show');
$router->post('/api/notes', 'NoteController@store');
$router->put('/api/notes/{id}', 'NoteController@update');
$router->delete('/api/notes/{id}', 'NoteController@destroy');

$router->dispatch();
