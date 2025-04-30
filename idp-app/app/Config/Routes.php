<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'LoginController::index');
$routes->post('auth/login', 'AuthController::login');
$routes->get('users-web', 'UserWebController::index');

// API Routes
$routes->group('api', function($routes) {
    $routes->post('users', 'UserController::index');
    $routes->post('users/create', 'UserController::create');
    $routes->post('users/update/(:num)', 'UserController::update/$1');
    $routes->post('users/delete/(:num)', 'UserController::delete/$1');
});

$routes->post('chat', 'ChatController::message');
