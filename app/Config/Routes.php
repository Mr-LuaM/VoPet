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
    $routes->post('changeAddress', 'UserController::changeAddress');
    $routes->get('getAnnouncements', 'UserController::getAnnouncements');
    $routes->get('getContacts', 'UserController::getContacts');
    $routes->get('petCounts', 'UserController::getPetCounts');
    $routes->get('recent-pets', 'UserController::getRecentPets');
    $routes->post('adopt', 'UserController::adopt');
    $routes->get('adoptionHistory', 'UserController::adoptionHistory');
});
$routes->group('admin', function($routes) {
    $routes->get('users', 'AdminController::getUsers');
    $routes->post('addUser', 'AdminController::addUser');
    $routes->post('updateUserRole', 'AdminController::updateUserRole');
    $routes->post('removeUserAccount', 'AdminController::removeUserAccount');
});
$routes->get('test', 'AuthController::test');
