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
                // Get users with only the necessary details for user management, excluding the current user
                $users = $this->users->select('user_id, email, role, fname, lname, status, last_login_at, last_login_ip, picture_url')
                                     ->where('user_id !=', $userId) // Exclude the user making the request
                                     ->findAll();
    
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
    public function getPets()
{

    $recentPets = $this->pets
    ->where('status', 'available')
    ->orderBy('created_at', 'desc')
    ->findAll(4); // Limit to the most recent 10 available pets

return $this->respond($recentPets);
}
public function addPet()
{
    helper(['form', 'url']); // Load form and URL helpers if not already loaded

    // Set up basic validation for text inputs
    $inputValidation = $this->validate([
        'name' => 'required',
        'age' => 'required|numeric',
        'species' => 'required',
        'breed' => 'required',
        'gender' => 'required',
        'info' => 'required',
        'photo' => [
            'uploaded[photo]',
            'mime_in[photo,image/jpg,image/jpeg,image/gif,image/png]',
            'max_size[photo,4096]', // Example size limit: 4MB
        ],
    ]);

    // Check if validation passed
    if (!$inputValidation) {
        return $this->fail($this->validator->getErrors());
    }

    // Process and move the uploaded file
    $file = $this->request->getFile('photo');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName(); // Generate a new, random filename
        $file->move(ROOTPATH . 'public/uploads/pets', $newName); // Move the file to the server
        $photoPath = '/uploads/pets/' . $newName; // Construct the path for storing in the database
    } else {
        return $this->fail('Invalid photo upload');
    }

    // Prepare data for insertion, including the path of the uploaded photo
    $data = [
        'name' => $this->request->getVar('name'),
        'age' => $this->request->getVar('age'),
        'species' => $this->request->getVar('species'),
        'breed' => $this->request->getVar('breed'),
        'gender' => $this->request->getVar('gender'),
        'info' => $this->request->getVar('info'),
        'photo' => $newName, // Use the stored path
        'status' => 'available', // Example status
    ];

    // Attempt to insert the data into the database
    if ($this->pets->insert($data)) {
        return $this->respondCreated(['message' => 'Pet added successfully'], 201);
    } else {
        return $this->failServerError('Failed to add pet');
    }
}

public function updatePet()
{
    helper(['form', 'url']);


    $rules = [
        'name' => 'required',
        'age' => 'required|numeric',
        'species' => 'required',
        'breed' => 'required',
        'gender' => 'required',
        'info' => 'required',
        // Conditional validation for 'photo'
    ];

    if (!$this->validate($rules)) {
        return $this->fail($this->validator->getErrors());
    }

    // Retrieving pet ID from the POST data
    $petId = $this->request->getPost('pet_id');
    if (!$petId) {
        return $this->fail('Pet ID is required for update', 400);
    }

    $file = $this->request->getFile('photo');
    $photoPath = null;
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads/pets', $newName);
        $photoPath = '/uploads/pets/' . $newName;
    }

    $dataToUpdate = [
        'name' => $this->request->getPost('name'),
        'age' => $this->request->getPost('age'),
        'species' => $this->request->getPost('species'),
        'breed' => $this->request->getPost('breed'),
        'gender' => $this->request->getPost('gender'),
        'info' => $this->request->getPost('info'),
    ];

    // Only update photo if a new one was uploaded
    if ($photoPath !== null) {
        $dataToUpdate['photo'] = $newName;
    }

    if ($this->pets->update($petId, $dataToUpdate)) {
        return $this->respondUpdated(['message' => 'Pet updated successfully']);
    } else {
        return $this->failServerError('Failed to update pet details');
    }
}

public function archivePet()
{
    $json = $this->request->getJSON(true);
    $petId = $json['pet_id'] ?? null;

    if (!$petId) {
        return $this->fail('Pet ID is required', 400);
    }


    // Assuming 'status' is a field in your pets table that can be 'active', 'archived', etc.
    $updateData = ['status' => 'archived'];

    if ($this->pets->update($petId, $updateData)) {
        return $this->respond(['message' => 'Pet archived successfully']);
    } else {
        return $this->failServerError('Failed to archive pet');
    }
}

public function getTransactions()
{

    try {
        $transactions = $this->transactions
        ->select('transactions.transaction_id, transactions.status, transactions.created_at, transactions.updated_at, 
                  pets.name AS pet_name, pets.age, pets.species, pets.breed, pets.status AS pet_status, pets.info, pets.photo, pets.gender,
                  users.fname, users.lname, users.email, users.contact_number, users.picture_url')
        ->join('pets', 'pets.pet_id = transactions.pet_id', 'left')
        ->join('users', 'users.user_id = transactions.user_id', 'left')
        ->where('transactions.status', 'requested') // Ensure the status is correctly quoted if it's a string
        ->findAll();
    
        return $this->respond([
            'status' => 200,
            'error' => null,
            'data' => $transactions
        ]);
    } catch (Exception $e) {
        // Handle exceptions
        return $this->respond([
            'status' => 500,
            'error' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}

public function approveTransaction()
{
    $db = \Config\Database::connect(); // Get the database connection instance

    try {
        $json = $this->request->getJSON();
        // Convert transaction_id to int
        $transactionId = isset($json->transaction_id) ? (int)$json->transaction_id : 0;
        $status = $json->status ?? '';

        // Start transaction
        $db->transStart();

        // Retrieve the transaction to get the associated pet ID
        $transaction = $this->transactions->find($transactionId);
        if (!$transaction) {
            return $this->failNotFound('Transaction not found');
        }

        // Update the transaction status to "approved"
        $this->transactions->update($transactionId, ['status' => $status]);


        // Complete the transaction
        $db->transComplete();

        if ($db->transStatus() === false) {
            // Transaction failed
            throw new \Exception('Transaction failed');
        }

        return $this->respondUpdated(['message' => 'Transaction approved']);
    } catch (\Exception $e) { // Use a backslash for global namespace if needed
        // Log the error for server diagnostics
        log_message('error', 'Transaction approval error: ' . $e->getMessage());

        // Rollback the transaction in case of error
        $db->transRollback();

        // Return a generic error message to the client for security reasons
        return $this->failServerError('An error occurred during the transaction approval process.');
    }
}
public function rejectTransaction()
{
    $db = \Config\Database::connect(); // Get the database connection instance

    try {
        $json = $this->request->getJSON();
        $transactionId = isset($json->transaction_id) ? (int)$json->transaction_id : 0;
        $status = 'denied'; // Assuming you're setting the status directly here

        if (!$transactionId) {
            return $this->failValidationError('Invalid transaction ID.');
        }

        $db->transStart(); // Begin transaction

        // Retrieve the transaction to get the associated pet ID
        $transaction = $this->transactions->find($transactionId);
        if (!$transaction) {
            return $this->failNotFound('Transaction not found.');
        }

        // Update the transaction's status to 'denied'
        $this->transactions->update($transactionId, ['status' => $status]);

        // Update the pet's status back to 'available'
        $petId = $transaction['pet_id'];
        $this->pets->update($petId, ['status' => 'available']);

        $db->transComplete(); // Attempt to commit the transaction

        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed to complete.');
        }

        return $this->respondUpdated(['message' => 'Transaction denied and pet status updated to available.']);
    } catch (\Exception $e) {
        $db->transRollback(); // Ensure rollback on error
        log_message('error', 'Rejection error: ' . $e->getMessage());
        return $this->failServerError('An error occurred during the rejection process.');
    }
}
public function getTransactionsHistory()
{
    try {
        // Define the statuses you want to include in the history
        $statuses = [ 'approved', 'denied', 'unclaimed', 'completed'];

        $transactions = $this->transactions
        ->select('transactions.transaction_id, transactions.status, transactions.created_at, transactions.updated_at, 
                  pets.name AS pet_name, pets.age, pets.species, pets.breed, pets.status AS pet_status, pets.info, pets.photo, pets.gender,
                  users.fname, users.lname, users.email, users.contact_number, users.picture_url')
        ->join('pets', 'pets.pet_id = transactions.pet_id', 'left')
        ->join('users', 'users.user_id = transactions.user_id', 'left')
        ->whereIn('transactions.status', $statuses)
        ->orderBy('transactions.created_at', 'DESC') // Add this line to sort by `created_at` in descending order
        ->findAll();

        return $this->respond([
            'status' => 200,
            'error' => null,
            'data' => $transactions
        ]);
    } catch (Exception $e) {
        // Handle exceptions
        return $this->respond([
            'status' => 500,
            'error' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}
public function markTransactionAsCompleted()
{
    $db = \Config\Database::connect(); // Get the database connection instance

    try {
        $json = $this->request->getJSON();
        // Convert transaction_id to int
        $transactionId = isset($json->transaction_id) ? (int)$json->transaction_id : 0;
        $status = $json->status ?? '';

        // Start transaction
        $db->transStart();

        // Retrieve the transaction to get the associated pet ID
        $transaction = $this->transactions->find($transactionId);
        if (!$transaction) {
            return $this->failNotFound('Transaction not found');
        }

        // Update the transaction status to "approved"
        $this->transactions->update($transactionId, ['status' => $status]);

        // Now, update the associated pet's status to "adopted"
        $petId = $transaction['pet_id'];
        $this->pets->update((int)$petId, ['status' => 'adopted']);

        // Complete the transaction
        $db->transComplete();

        if ($db->transStatus() === false) {
            // Transaction failed
            throw new \Exception('Transaction failed');
        }

        return $this->respondUpdated(['message' => 'Transaction approved and pet status updated to adopted']);
    } catch (\Exception $e) { // Use a backslash for global namespace if needed
        // Log the error for server diagnostics
        log_message('error', 'Transaction approval error: ' . $e->getMessage());

        // Rollback the transaction in case of error
        $db->transRollback();

        // Return a generic error message to the client for security reasons
        return $this->failServerError('An error occurred during the transaction approval process.');
    }
}
public function markTransactionAsUnclaimed()
{
    $db = \Config\Database::connect(); // Get the database connection instance

    try {
        $json = $this->request->getJSON();
        $transactionId = isset($json->transaction_id) ? (int)$json->transaction_id : 0;
        $status = $json->status ?? '';

        if (!$transactionId) {
            return $this->failValidationError('Invalid transaction ID.');
        }

        $db->transStart(); // Begin transaction

        // Retrieve the transaction to get the associated pet ID
        $transaction = $this->transactions->find($transactionId);
        if (!$transaction) {
            return $this->failNotFound('Transaction not found.');
        }

        // Update the transaction's status to 'denied'
        $this->transactions->update($transactionId, ['status' => $status]);

        // Update the pet's status back to 'available'
        $petId = $transaction['pet_id'];
        $this->pets->update($petId, ['status' => 'available']);

        $db->transComplete(); // Attempt to commit the transaction

        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed to complete.');
        }

        return $this->respondUpdated(['message' => 'Transaction denied and pet status updated to available.']);
    } catch (\Exception $e) {
        $db->transRollback(); // Ensure rollback on error
        log_message('error', 'Rejection error: ' . $e->getMessage());
        return $this->failServerError('An error occurred during the rejection process.');
    }
}

// In AdminController.php or a relevant controller
public function userLocations() {
    $builder = $this->db->table('users');
    // Adjust the selection to extract and format the municipality and province
    $builder->select("CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(address, ',', 3), ',', -1), ',', 1), ', ', SUBSTRING_INDEX(SUBSTRING_INDEX(address, ',', 2), ',', -1)) AS location, COUNT(DISTINCT transactions.pet_id) AS pet_count");
    $builder->join('transactions', 'transactions.user_id = users.user_id', 'left');
    $builder->where('users.address !=', ''); // Ensure the address is not empty
    $builder->groupBy('location'); // Group by the new 'location' format
    $query = $builder->get();

    // Fetch the result set as an array
    $results = $query->getResultArray();

    // Return the result set as JSON
    return $this->response->setJSON($results);
}










}
