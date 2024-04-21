<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('register', 'AuthController::register');
    $routes->get('users', 'AuthController::getAllUsers', ['filter' => 'authFilter']);
    $routes->get('profile', 'AuthController::getProfile', ['filter' => 'authFilter']);
    $routes->post('logout', 'AuthController::logout', ['filter' => 'authFilter']);
});

$routes->group('', function ($routes) {
    $routes->get('/', 'WebController::login');
    $routes->get('register', 'WebController::register');
    $routes->post('login', 'WebController::attemptLogin');
    $routes->get('dashboard', 'WebController::dashboard');
});
