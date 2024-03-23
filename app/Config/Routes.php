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
    $routes->post('resetpassword', 'AuthController::resetPassword');
    $routes->post('save-token', 'AuthController::saveToken');


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
    $routes->get('medicalHistory', 'UserController::medicalHistory');
    $routes->get('messages', 'UserController::messages');
    $routes->post('sendMessages', 'UserController::sendMessages');
    $routes->post('petture', 'UserController::petture');
    $routes->get('petSavedHistory', 'UserController::petSavedHistory');




});
$routes->group('admin', function($routes) {
    $routes->get('users', 'AdminController::getUsers');
    $routes->post('addUser', 'AdminController::addUser');
    $routes->post('updateUserRole', 'AdminController::updateUserRole');
    $routes->post('removeUserAccount', 'AdminController::removeUserAccount');
    $routes->post('addNewClinic', 'AdminController::addNewClinic');

  
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
    $routes->get('medical-history', 'AdminController::getMedicalHistory');
    $routes->post('markTransactionAsNotCorrect', 'AdminController::markTransactionAsNotCorrect');
    $routes->post('addMedicalHistory', 'AdminController::addMedicalHistory');
    $routes->post('messages', 'AdminController::messages');
    $routes->post('sendMessages', 'AdminController::sendMessages');
    $routes->get('petLocations', 'AdminController::getPetLocations');
    $routes->post('markPetAsRescued', 'AdminController::markAsRescued');
    $routes->get('PatientPetsMedicalHistory', 'AdminController::getPatientPetsMedicalHistory');
    $routes->post('addMedicalHistoryforExistingpets', 'AdminController::addMedicalHistoryForExistingPets');


});

$routes->group('form', function($routes) {
    $routes->get('pets', 'FormController::getPets'); // Maps /form/pets to FormController::getPets method
    $routes->get('getClientPets', 'FormController::getClientPets');
    $routes->get('getClientUsers', 'FormController::getClientUsers');
    $routes->get('clinics', 'FormController::clinics');



});
$routes->group('api', function($routes) {
    $routes->get('monthlyAdoptions', 'ApiController::monthlyAdoptions'); // Maps /form/pets to FormController::getPets method
    $routes->get('monthlyRescues', 'ApiController::monthlyRescues'); // Maps /form/pets to FormController::getPets method

});


$routes->get('test', 'AuthController::test');
