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
    $routes->get('pets', 'AdminController::getPets');
    $routes->post('addPet', 'AdminController::addPet');
    $routes->post('updatePet', 'AdminController::updatePet');
    $routes->post('archivePet', 'AdminController::archivePet');
    $routes->get('transactions', 'AdminController::getTransactions');
    $routes->post('approveTransaction', 'AdminController::approveTransaction');
    $routes->post('rejectTransaction', 'AdminController::rejectTransaction');
    $routes->get('transactionsHistory', 'AdminController::getTransactionsHistory');
    $routes->post('markTransactionAsCompleted', 'AdminController::markTransactionAsCompleted');
    $routes->post('markTransactionAsUnclaimed', 'AdminController::markTransactionAsUnclaimed');
    $routes->get('user-locations', 'AdminController::userLocations');
    $routes->get('vet-info', 'AdminController::getVetInfo');
    $routes->post('updateVetInfo', 'AdminController::updateVetInfo');
    $routes->get('getAnnouncements', 'AdminController::getAnnouncements');
    $routes->post('deleteAnnouncement', 'AdminController::deleteAnnouncement');
    $routes->post('updateAnnouncement', 'AdminController::updateAnnouncement');
    $routes->post('addAnnouncement', 'AdminController::addAnnouncement');





});
$routes->get('test', 'AuthController::test');
