<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth Routes
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::attemptRegister');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

// Protected Routes
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    // Catalog routes
    $routes->get('catalog/create', 'CatalogController::create');
    $routes->post('catalog/upload', 'CatalogController::uploadFiles');
    $routes->post('catalog/analyze/(:num)', 'CatalogController::analyze/$1');
    $routes->get('catalog/review/(:num)', 'CatalogController::review/$1');
    $routes->post('catalog/feedback', 'CatalogController::feedback');
    $routes->post('catalog/approve', 'CatalogController::approvePage');
    $routes->get('catalog/finalize/(:num)', 'CatalogController::finalize/$1');
    $routes->get('catalog/sse/(:num)', 'CatalogController::sse/$1');
    $routes->get('catalog/image/(:num)', 'CatalogController::serveImage/$1');
    $routes->get('catalog/view/(:num)', 'CatalogController::view/$1');
    $routes->get('catalog/download/(:num)', 'ExportController::download/$1');
    $routes->get('catalog/pdf/(:num)', 'ExportController::pdf/$1');

    // Payment
    $routes->get('payment/charge', 'PaymentController::charge');
    $routes->post('payment/process', 'PaymentController::processCharge');
    $routes->get('payment/callback', 'PaymentController::callback'); // Datees often uses GET
    $routes->post('payment/callback', 'PaymentController::callback'); // Or POST

    // Queue trigger
    $routes->get('queue/process', 'QueueController::process');
});

// Admin Routes
$routes->group('admin', ['filter' => 'admin', 'namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Users
    $routes->get('users', 'UserController::index');
    $routes->post('users/updateWallet', 'UserController::updateWallet');

    // Catalogs
    $routes->get('catalogs', 'CatalogController::index');
    $routes->get('catalogs/download/(:num)', 'CatalogController::download/$1'); // Phase 9 hook or admin specific

    // Settings
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings/save', 'SettingsController::save');
});
