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

    protected $firebaseServerKey = 'AAAAGdrwYnc:APA91bGXw5cv3F3unpb56ySKuWglwbUrCNnYbsyZ7RxDCHLTHSIapFcRmcNoOpWIYIBY928el1PEN39VOyK_kqu1T13Pq78GqvrTg8T8EDmAxFY2Iv0S_B9E9Jf-TDL2RTaSZyXlx-IX';

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
public function getVetInfo()
{
   
    $data = $this->officeContacts->findAll();

    return $this->respond($data);
}
public function updateVetInfo()
{
    $json = $this->request->getJSON(true);

    $types = [
        'email' => 'email',
        'contact_number' => 'phone',
        'address' => 'address',
        'social_media_link' => 'social media',
    ];

    $successUpdates = [];
    $failedUpdates = [];

    foreach ($types as $key => $type) {
        if (isset($json[$key])) {
            $builder = $this->db->table('office_contacts'); // Assuming 'office_contacts' is your table name
            $builder->set('value', $json[$key]);
            $builder->where('type', $type);
            $result = $builder->update();

            if ($result) {
                $successUpdates[] = $type;
            } else {
                $failedUpdates[] = $type;
            }
        }
    }

    if (empty($failedUpdates)) {
        return $this->respondUpdated(['message' => 'Vet info updated successfully']);
    } else {
        return $this->fail('Failed to update: ' . join(', ', $failedUpdates));
    }
}
public function getAnnouncements()
{
    $builder = $this->db->table('announcements');
    
    // Perform a join with the users table
    $builder->select('announcements.announcement_id, announcements.title, announcements.content, announcements.created_at, announcements.updated_at, announcements.image_url, users.fname, users.lname, users.picture_url');
    $builder->join('users', 'users.user_id = announcements.user_id');
    
    // Order by created_at column in descending order
    $builder->orderBy('announcements.created_at', 'DESC');
    
    $announcements = $builder->get()->getResultArray();

    return $this->respond(['items' => $announcements]);
}

public function deleteAnnouncement()
    {
        // Get the announcementId from the request data
        $announcementId = $this->request->getVar('announcementId');

        // Check if the announcementId is provided
        if (empty($announcementId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Announcement ID is missing']);
        }

        // Instantiate the AnnouncementModel


        try {
            // Delete the announcement from the database
            $this->announcements->delete($announcementId);

            return $this->response->setStatusCode(200)->setJSON(['message' => 'Announcement deleted successfully']);
        } catch (\Exception $e) {
            // Handle deletion error
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error deleting announcement']);
        }
    }
    public function updateAnnouncement()
    {
        // Validate incoming request if needed
        $validationRules = [
            'title' => 'required',
            'content' => 'required',
            'announcement_id' => 'required|numeric', // Change 'announcement_id' to 'id'
        ];
    
        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
    
        // Extract data from the request
        $announcementId = $this->request->getVar('announcement_id'); // Change 'announcement_id' to 'id'
        $title = $this->request->getVar('title');
        $content = $this->request->getVar('content');
    
        // Load the AnnouncementModel instance
        $announcement = $this->announcements->find($announcementId);
    
        if (!$announcement) {
            return $this->failNotFound('Announcement not found');
        }
    
        // Prepare data to update
        $dataToUpdate = [
            'title' => $title,
            'content' => $content,
        ];
    
        // Include user id in the announcement data
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;
    
        // Retrieve the role of the user making the request
        $user = $this->users->find($userId);
    
        // Add user id to the announcement data
        $dataToUpdate['user_id'] = $user['user_id'];
    
        // Update announcement fields
        $this->announcements->update($announcementId, $dataToUpdate);
    
        // Check if the announcement was successfully updated
        $updatedAnnouncement = $this->announcements->find($announcementId);
        if ($updatedAnnouncement) {
            return $this->respondUpdated(['message' => 'Announcement updated successfully']);
        } else {
            return $this->failServerError('Failed to update announcement');
        }
    }
    
    
    public function addAnnouncement()
    {
        // Validate incoming request if needed
        $validationRules = [
            'title' => 'required',
            'content' => 'required',
            // You might have additional validation rules here
        ];
    
        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
    
        // Extract data from the request
        $title = $this->request->getVar('title');
        $content = $this->request->getVar('content');
    
        // Include user id in the announcement data
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;
    
        // Prepare data to add announcement
        $dataToAdd = [
            'title' => $title,
            'content' => $content,
            'user_id' => $userId, // Use the decoded user id directly
            // You can add more fields here if needed
        ];
    
        // Insert the new announcement
        if ($this->announcements->insert($dataToAdd)) {
            // Retrieve FCM tokens of users and send notifications
            $tokens = $this->getFCMTokens();
            $title = 'New Announcement';
            $body = 'Check out the latest announcement: ' .  $dataToAdd['title'];    
            $result = $this->sendPushNotification($tokens, $title, $body);
    
            
            // Handle the result if needed
            // For example, log the result or return a response based on success/failure
    
            return $this->respondCreated(['message' =>  $result]);
        } else {
            return $this->failServerError('Failed to add announcement');
        }
    }
    
    private function getFCMTokens()
    {
        $users = $this->users->findAll(); // Assuming `$this->users` is your user model instance
        $tokens = [];
    
        foreach ($users as $user) {
            if (!empty($user['fcm_token'])) {
                $tokens[] = $user['fcm_token'];
            }
        }
    
        return $tokens;
    }
    public function sendPushNotification($tokens, $title, $body)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = [
            'Authorization: key=' . $this->firebaseServerKey,
            'Content-Type: application/json'
        ];

        $fields = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }


    




}
