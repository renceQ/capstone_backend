<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/getData', 'MainController::getData');
$routes->get('/getDatas', 'MainController::getDatas');
$routes->get('/getevent', 'MainController::getevent');
$routes->get('/getcat', 'MainController::getcat');
$routes->post('/save', 'MainController::save');
$routes->get('/getUserData/(:any)', 'MainController::getUserData/$1');
$routes->post('/saveBooking', 'MainController::saveBooking');
$routes->post('/del', 'MainController::del');
$routes->post('/savecateg', 'MainController::savecateg');
$routes->match(['post', 'get'],'/getsize', 'MainController::getsize');
$routes->post('/editcateg', 'MainController::editcateg');
$routes->match(['post', 'get'],'/api/login', 'MainController::login');
$routes->match(['post', 'get'],'/api/register', 'MainController::register');

// get products by category
$routes->get('getProductsByCategory/(:num)', 'MainController::getProductsByCategory/$1');

// Update product by ID
$routes->put('updateProduct/(:num)', 'MainController::updateProduct/$1');
$routes->post('updateItem/(:any)', 'MainController::updateItem/$1');

//audit
$routes->get('getaudith/(:num)', 'MainController::getaudith/$1');

//get sales
$routes->get('getsales/(:num)', 'MainController::getsales/$1');


//save order product
$routes->post('/placeOrder', 'MainController::placeOrder');
$routes->post('/addtocart', 'MainController::addtocart');

//get order request
$routes->get('getOrder', 'MainController::getOrder');

//update status
$routes->post('updateOrderStatus/(:any)', 'MainController::updateOrderStatus/$1');
$routes->post('updateOrderStatus', 'MainController::updateOrderStatus');
$routes->post('updateItem/(:any)', 'MainController::updateItem/$1');


//update event status

$routes->match(['post', 'get'], 'updateEventStatus', 'MainController::updateEventStatus');
$routes->match(['post', 'get'], 'checkUsername', 'MainController::checkUsername');


//sample data for practice
$routes->get('/getgeData', 'MainController::getgeData');
//notif
$routes->get('/getNotif', 'MainController::getNotif');
$routes->match(['post', 'get'], 'updateNotifStatus/(:any)', 'MainController::updateNotifStatus/$1');
$routes->post('updateNotifStatus/(:any)', 'MainController::updateNotifStatus/$1');
$routes->get('updateNotifStatus/(:any)', 'MainController::updateNotifStatus/$1');








