<?php

use App\Controllers\UserController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

$routes->group('auth', function($routes) {
    $routes->post('signup', 'AuthController::signup');
    $routes->post('verify-otp', 'AuthController::verifyOtp');
    $routes->post('resend-otp', 'AuthController::resendOtp');
    $routes->post('login', 'AuthController::login');
    $routes->post('forgotpassword', 'AuthController::forgotPassword');
    $routes->post('resetpassword', 'AuthController::resetPassword');

    $routes->get('fetchuserdetails', 'AuthController::userDetails');
});
$routes->group('user', function($routes) {
    $routes->post('changePassword', 'UserController::changePassword');
    $routes->post('changeContact', 'UserController::changeContact');
    $routes->post('changePersonalInformation', 'UserController::changePersonalInformation');
    $routes->post('changeProfilePicture', 'UserController::changeProfilePicture');
    $routes->get('getAnnouncements', 'UserController::getAnnouncements');
    $routes->get('getContacts', 'UserController::getContacts');
    $routes->get('petCounts', 'UserController::getPetCounts');
    $routes->get('recent-pets', 'UserController::getRecentPets');



});
$routes->get('test', 'AuthController::test');
