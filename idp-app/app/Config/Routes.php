<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::index');
$routes->post('auth/login', 'AuthController::login');

$routes->get('list-users', 'UserController::index');
$routes->get('users', 'UserController::getAll');
$routes->post('users', 'UserController::create');
$routes->put('users/(:num)', 'UserController::update/$1');
$routes->delete('users/(:num)', 'UserController::delete/$1');

$routes->post('chat', 'ChatController::message');
