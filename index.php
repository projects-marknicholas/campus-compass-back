<?php
require 'config.php';
require 'router.php';

// Controllers
require 'controllers/auth.php';

// Initialize Router
$router = new Router();

// Post Requests
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');
$router->put('/api/auth/user', 'AuthController@update_user');

// Dispatch the request
$router->dispatch();
?>