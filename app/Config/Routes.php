<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'ProjectController::index');

$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'ProjectController::admin');
    $routes->match(['get', 'post'], 'addedit', 'ProjectController::addedit');
    $routes->match(['get', 'post'], 'addedit/(:num)', 'ProjectController::addedit/$1');
    $routes->get('delete/(:num)', 'ProjectController::delete/$1');
});

// Login function
$routes->get('/login', 'Auth::login');
$routes->post('/auth/processLogin', 'Auth::processLogin');
$routes->get('/logout', 'Auth::logout');

$routes->match(['get', 'post'], 'signup', 'ProjectController::signup');

// restaurant view with authentication
$routes->group('restaurant', ['filter' => 'restaurant'], function($routes) {
    $routes->get('(:num)', 'ProjectController::restaurant/$1');
    $routes->get('(:num)/ordermanagement', 'ProjectController::restaurantOrderManagement/$1');
});

// Customer ordering pages
$routes->get('/ordering', 'ProjectController::ordering');
$routes->get('/orderstatus', 'ProjectController::orderstatus');
$routes->get('/orderstatus/(:num)', 'ProjectController::orderstatus/$1');

// API with authorisation
$routes->group('api', ['filter' => 'restaurant'], function ($routes) {
    $routes->match(['get', 'post'], 'dishCategory', 'Api::dishCategory');
    $routes->match(['get', 'post'], 'dishCategory/(:num)', 'Api::dishCategory/$1');
    $routes->get('dishCategory/delete/(:num)', 'Api::deleteDishCategory/$1');

    $routes->match(['get', 'post'], 'customisationOption', 'Api::customisationOption');
    $routes->match(['get', 'post'], 'customisationOption/(:num)', 'Api::customisationOption/$1');
    $routes->get('customisationOption/delete/(:num)', 'Api::deleteCustomisationOption/$1');

    $routes->match(['get', 'post'], 'dish', 'Api::dish');
    $routes->match(['get', 'post'], 'dish/(:num)', 'Api::dish/$1');
    $routes->get('dish/delete/(:num)', 'Api::deleteDish/$1');

    $routes->match(['get', 'post'], 'table', 'Api::table');
    $routes->match(['get', 'post'], 'table/(:num)', 'Api::table/$1');
    $routes->get('table/delete/(:num)', 'Api::table/$1');
});

// public api
$routes->group('api', function ($routes) {
    $routes->get('getAllDishCategory/(:num)', 'Api::getAllDishCategory/$1');
    $routes->get('getAllCustomisationOptions/(:num)', 'Api::getAllCustomisationOptions/$1');
    $routes->get('getCustomisationOptionRowData/(:num)/(:num)', 'Api::getCustomisationOptionRowData/$1/$2');
    $routes->get('getDishRowData/(:num)/(:num)', 'Api::getDishRowData/$1/$2');

    $routes->get('getDishDetails/(:num)', 'Api::getDishDetails/$1');

    $routes->post('calculateDishesPrice', 'Api::calculateDishesPrice');
    $routes->post('sendOrder', 'Api::sendOrder');
    $routes->get('getOrderDetails/(:num)', 'Api::getOrderDetails/$1');

    $routes->post('changeOrderStatus', 'Api::changeOrderStatus');

});

