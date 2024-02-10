<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Request;
use CodeIgniter\API\ResponseTrait;
use Exception; 
use App\Traits\SendVerificationEmail;

class AdminController extends BaseController
{
    use SendVerificationEmail;
    use ResponseTrait;
    private $users;
    private $announcements;

    private$officeContacts;
    
    private$pets;

    private $transactions;
    protected $db;
    public function __construct(){
        $this->users = new \App\Models\UserModel();
        $this->announcements = new \App\Models\AnnouncementModel();
        $this->officeContacts = new \App\Models\OfficeContactsModel();
        $this->pets = new \App\Models\PetModel();
        $this->transactions = new \App\Models\TransactionModel();

        $this->db = \Config\Database::connect();
    }

public function getUsers()
{
    try {
        // Decode the JWT token to get the user ID
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;

        // Retrieve the role of the user making the request
        $user = $this->users->find($userId);
        $role = $user['role']; // Assuming 'role' is a key in the user array

        // Check if the user has the admin role
        if ($role === 'admin') {
            // Get users with only the necessary details for user management
            $users = $this->users->select('user_id, email, role, fname, lname, status, last_login_at, last_login_ip, picture_url')
                                 ->findAll();

            // You can apply filtering, sorting, and pagination as needed

            // Respond with the users data
            return $this->respond([
                'status' => 200,
                'error' => null,
                'data' => $users
            ]);
        } else {
            // If the user is not an admin, return a permission error or empty data
            return $this->respond([
                'status' => 403,
                'error' => 'Access denied',
                'data' => []
            ], 403);
        }
    } catch (Exception $e) {
        // Handle exceptions, such as token decoding issues
        return $this->respond([
            'status' => 500,
            'error' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}

public function addUser(){
 
        $rules = [
            'fname' => 'required|min_length[2]',
            'lname' => 'required|min_length[2]',
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'This email is already registered. Please use another email.'
                ]
            ],            'password' => 'required|min_length[8]',
            'birthdate' => 'required',
            'sex' => 'required',
            'contact_number' => 'required|min_length[10]',
            'role' => 'required',
        ];

        $data = $this->request->getJSON(true);
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Hash password before saving
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

         // Generate a 6-digit verification token
    $verificationToken = rand(100000, 999999); // Generates a number between 100000 and 999999
    $data['verification_token'] = $verificationToken;
    $data['token_expires_at'] = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Token expires in 10 minutes
        $this->users->save($data);

 // Prepare the response data (excluding sensitive information like password)
 $userId = $this->users->getInsertID();
  // Customize the email subject and body
  $emailSubject = 'Account Verification';
  $emailBody = 'Your verification code is: ' . $verificationToken . '<br>Please enter this code to verify your email address. This code will expire in 10 minutes.';

  // Send the verification email with customized content
  $this->_sendVerificationEmail($data['email'], $emailSubject, $emailBody);

 $responseData = [
     'userId' => $userId,
     'fname' => $data['fname'],
     'lname' => $data['lname'],
     'email' => $data['email'],
     // Include any other non-sensitive data as needed
 ];

 return $this->respondCreated([
     'message' => 'An admin registered you.',
     'data' => $responseData
 ]);   
 }

 public function updateUserRole()
    {

        $json = $this->request->getJSON();

        // Extract user_id and role from the request
        $userId = $json->user_id ?? null;
        $newRole = $json->role ?? null;

        // Validation example (add more as required)
        if (!$userId || !$newRole) {
            return $this->fail('Missing user ID or role', 400);
        }

        // Update the user's role
        $updateData = ['role' => $newRole];
        $updated = $this->users->update($userId, $updateData);

        if ($updated) {
            return $this->respondUpdated(['message' => 'Role updated successfully']);
        } else {
            // handle failure (e.g., user not found)
            return $this->failNotFound('No user found with the specified ID');
        }
    }

    public function removeUserAccount()
    {
        $json = $this->request->getJSON();
    
        // Extract user_id from the request
        $userId = $json->user_id ?? null;
    
        // Validate the presence of user_id
        if (!$userId) {
            return $this->fail('Missing user ID', 400);
        }
    
        // Assuming 'status' is a column in your 'users' table where 'banned' indicates a deactivated account
        $updated = $this->users->update($userId, ['status' => 'suspended']);
    
        if ($updated) {
            return $this->respondUpdated(['message' => 'Account deactivated successfully']);
        } else {
            // Handle failure (e.g., user not found or update failed)
            return $this->failNotFound('No user found with the specified ID');
        }
    }
    

    
}
