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

class UserController extends ResourceController
{
    use ResponseTrait;
    private $users;
    private $announcements;

    private$officeContacts;
    
    private$pets;

    private $transactions;

    private $medicalHistory;
    private $messages;
    private $petLocations;
    protected $db;
    public function __construct(){
        $this->users = new \App\Models\UserModel();
        $this->announcements = new \App\Models\AnnouncementModel();
        $this->officeContacts = new \App\Models\OfficeContactsModel();
        $this->pets = new \App\Models\PetModel();
        $this->transactions = new \App\Models\TransactionModel();
        $this->medicalHistory = new \App\Models\MedicalHistoryModel();
        $this->messages = new \App\Models\MessagesModel();
        $this->petLocations = new \App\Models\PetLocationsModel();
        $this->db = \Config\Database::connect();
    }
    
    public function changePassword()
{
    try {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = explode(' ', $authHeader)[1] ?? '';

        if (!$token) {
            return $this->failUnauthorized('Token required');
        }

        // Assuming JWT and Key are correctly imported
        $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub; // Correctly define $userId from the decoded token

        $json = $this->request->getJSON();
        $currentPassword = $json->currentPassword ?? '';
        $newPassword = $json->password ?? '';
        $confirmPassword = $json->confirmPassword ?? '';

        if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[\W_]/', $newPassword)) {
            return $this->fail('New password does not meet complexity requirements.', 400);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->fail('The new passwords do not match.', 400);
        }

        $user = $this->users->find($userId);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return $this->fail('Current Password is wrong. Forget Password if you cant remember', 401);
        }

        $this->users->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        return $this->respondUpdated(['message' => 'Password changed successfully.']);
    } catch (Exception $e) {
        return $this->failServerError('An error occurred. ' . $e->getMessage()); // Optionally log the exception message for debugging
    }
}
public function changeContact()
{
    try {
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;

        $json = $this->request->getJSON(true);
        $contactNumber = $json['contact_number'] ?? '';

        if (empty($contactNumber)) {
            return $this->fail('Contact number is required.', 400);
        }

        // Assuming your UserModel is referenced as $this->users and handles validation
        if (!$this->users->update($userId, ['contact_number' => $contactNumber])) {
            return $this->fail('Failed to update contact information.', 500);
        }

        return $this->respondUpdated(['message' => 'Contacts changed successfully.']);
    } catch (Exception $e) {
        return $this->failServerError('An error occurred.' . $e->getMessage());
    }
}
public function changePersonalInformation()
{
    try {
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;

        $json = $this->request->getJSON(true);
        $fname = $json['fname'] ?? '';
        $lname = $json['lname'] ?? '';
        $birthdate = $json['birthdate'] ?? '';
        $sex = $json['sex'] ?? '';

        // Basic validation (consider more robust validation rules)
        if (empty($fname) || empty($lname) || empty($birthdate) || empty($sex)) {
            return $this->fail('All fields are required.', 400);
        }

        // Assuming your UserModel is referenced as $this->users
        if (!$this->users->update($userId, [
            'fname' => $fname,
            'lname' => $lname,
            'birthdate' => $birthdate,
            'sex' => $sex
        ])) {
            return $this->fail('Failed to update personal information.', 500);
        }

        return $this->respondUpdated(['message' => 'Personal information updated successfully.']);
    } catch (Exception $e) {
        return $this->failServerError('An error occurred: ' . $e->getMessage());
    }
}
public function changeProfilePicture()
{
    try {
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;

        $file = $this->request->getFile('profile_url');
        if (!$file->isValid() || $file->hasMoved()) {
            throw new \RuntimeException($file->getErrorString().'('.$file->getError().')');
        }

        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName); // Ensure this directory exists and is writable

        if (!$this->users->update($userId, ['picture_url' =>  $newName])) {
            return $this->failServerError('Failed to update user profile.');
        }

        return $this->respondUpdated(['message' => 'Profile picture updated successfully']);
    } catch (Exception $e) {
        log_message('error', 'Profile picture update error: ' . $e->getMessage());
        return $this->failServerError('An error occurred: ' . $e->getMessage());
    }
}

public function getAnnouncements(){
    {
        $builder = $this->db->table('announcements');
        
        // Perform a join with the users table
        $builder->select('announcements.title, announcements.content, announcements.created_at, announcements.updated_at, announcements.image_url, users.fname, users.lname, users.picture_url');
        $builder->join('users', 'users.user_id = announcements.user_id');
        $builder->orderBy('announcements.created_at', 'DESC');

        $announcements = $builder->get()->getResultArray();

        return $this->respond(['items' => $announcements]);
    }
}
public function getContacts()
{
    $contacts = $this->officeContacts->findAll();
    
    return $this->respond(['contacts' => $contacts]);
}
public function getPetCounts()
{
    // Extract the user ID from the JWT token
    $jwt = $this->request->getHeaderLine('Authorization');
    $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
    $userId = $decoded->sub;

    // Count the available pets from the pets table
    $availableQuery = $this->db->query("SELECT COUNT(*) AS available FROM pets WHERE status = 'available'");
    $availableResult = $availableQuery->getRow();

    // Count the pets with status 'processing' from the transactions table
    $processingQuery = $this->db->query("SELECT COUNT(*) AS processing FROM transactions WHERE user_id = $userId AND status = 'requested'");
    $processingResult = $processingQuery->getRow();

    // Count the pets with status 'adopted' from the transactions table
    $adoptedQuery = $this->db->query("SELECT COUNT(*) AS adopted FROM transactions WHERE user_id = $userId AND status = 'completed'");
    $adoptedResult = $adoptedQuery->getRow();

    // Combine the results into a single array
    $petCounts = [
        'available' => $availableResult->available,
        'processing' => $processingResult->processing,
        'adopted' => $adoptedResult->adopted
    ];

    // Return the data as JSON
    return $this->respond($petCounts, 200);
}


public function getRecentPets()
{

    $recentPets = $this->pets
    ->where('status', 'available')
    ->orderBy('created_at', 'desc')
    ->findAll(4); // Limit to the most recent 10 available pets

return $this->respond($recentPets);
}

public function adopt()
{
    // Ensure that the pet_id is provided in the request body
    $petId = $this->request->getVar('pet_id');
    if (empty($petId)) {
        return $this->fail('Missing pet_id in the request body', 400);
    }

    // Authenticate the user and verify authorization
    $jwt = $this->request->getHeaderLine('Authorization');
    $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
    $userId = $decoded->sub;

    // Process the adoption
    $pet = $this->pets->find($petId);
    if (!$pet) {
        return $this->fail('Pet not found', 404);
    }

    // Update the pet's status to 'pending' or another appropriate status
    $this->pets->set('status', 'pending')->where('pet_id', (int)$petId)->update();

    // Create a new adoption transaction

    $transactionData = [
        'user_id' => $userId,
        'pet_id' => $petId,
        'status' => 'requested' // Or 'pending', depending on your status terminology
    ];
    $this->transactions->insert($transactionData);

    // Optionally, perform additional actions (e.g., sending notifications)
     // Insert a message into the messages table
     $receiverId = $this->users->select('user_id')->where('role', 'admin')->first();
     $messageData = [
         'sender_id' => $userId,
         'receiver_id' => $receiverId,
         'content' => 'Adoption requested for pet ID: ' . $petId . ' named '. $pet['name'] ,
     ];
     $this->messages->insert($messageData);

    // Send a success response
    return $this->respond(['message' => 'Pet adoption requested successfully'], 200);
}

public function changeAddress()
{
    $jwt = $this->request->getHeaderLine('Authorization');
    $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
    $userId = $decoded->sub;


    $rules = [
        'region' => 'required',
        'province' => 'required',
        'municipality' => 'required',
        'barangay' => 'required',
        'zipcode' => 'required|numeric|max_length[5]'
    ];

    if ($this->validate($rules)) {
        $fullAddress = $this->request->getVar('region') . ', ' .
                       $this->request->getVar('province') . ', ' .
                       $this->request->getVar('municipality') . ', ' .
                       $this->request->getVar('barangay') . ', ' .
                       $this->request->getVar('zipcode');

        $data = ['address' => $fullAddress];

        if ($this->users->update($userId, $data)) {
            return $this->respondUpdated(['message' => 'Address changed successfully']);
        } else {
            return $this->failServerError('Could not update the address');
        }
    } else {
        return $this->fail($this->validator->getErrors());
    }
}
public function adoptionHistory() {
    try {
        // Decode the JWT token to get the user ID
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;

        // Fetch adoption and medical history data from the database
        $adoptionHistory = $this->db->table('transactions')
            ->join('pets', 'transactions.pet_id = pets.pet_id')
            ->join('medical_history', 'pets.pet_id = medical_history.pet_id', 'left') // Assuming you have a medical history table
            ->where('transactions.user_id', $userId)
            ->select('pets.pet_id, pets.name, pets.age, pets.species, pets.breed, pets.photo, transactions.status, transactions.created_at, transactions.updated_at, medical_history.medical_condition, medical_history.medication, medical_history.dosage') // Add columns from medical history table as needed
            ->orderBy('transactions.created_at', 'DESC') // Sort by the most recent creation date
            ->get()
            ->getResultArray();

        // Return adoption and medical history data as JSON response
        return $this->respond($adoptionHistory);
    } catch (Exception $e) {
        // Handle exceptions (e.g., database errors)
        return $this->failServerError('An error occurred');
    }
}
public function medicalHistory()
{
    // Decode the JWT token to get the user ID
    $jwt = $this->request->getHeaderLine('Authorization');
    $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
    $userId = $decoded->sub;

    // Fetch transactions for the user

    $transactions = $this->transactions->where('user_id', $userId)->findAll();

    // Extract pet IDs from transactions
    $petIds = array_column($transactions, 'pet_id');

    // Fetch pets' details using pet IDs

    $pets = $this->pets->whereIn('pet_id', $petIds)->findAll();

    // Fetch medical history for each pet

    foreach ($pets as &$pet) {
        $pet['medical_history'] = $this->medicalHistory->where('pet_id', $pet['pet_id'])->findAll();
    }

    // Return the response
    return $this->respond($pets);
}
public function messages()
{
    // Include user id in the announcement data
    $jwt = $this->request->getHeaderLine('Authorization');
    $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
    $loggedInUserId = $decoded->sub;

    // Get the receiver_id from the request body
    $receiverId = $this->users->select('user_id')->where('role', 'admin')->first();

    // Fetch messages exchanged between the logged-in user and the specified receiver
    $messages = $this->db->table('messages')
        ->select('message_id, sender_id, receiver_id, content, created_at')
        ->where('sender_id', $loggedInUserId)
        ->where('receiver_id', $receiverId)
        ->orWhere('sender_id', $receiverId)
        ->where('receiver_id', $loggedInUserId)
        ->orderBy('created_at', 'ASC')
        ->get()
        ->getResult();

    // Return the fetched messages as a JSON response
    return $this->respond($messages);
}

public function sendMessages()
{
    // Include user id in the announcement data
    $jwt = $this->request->getHeaderLine('Authorization');
    $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
    $loggedInUserId = $decoded->sub;

    // Get the content, sender_id, created_at, and receiver_id from the request body
    $content = $this->request->getVar('content');
    $createdAt = $this->request->getVar('created_at');
    $receiverId = $this->users->select('user_id')->where('role', 'admin')->first();;

    // Insert the message into the database
    $this->messages->insert([
        'content' => $content,
        'sender_id' => $loggedInUserId,
        'created_at' => $createdAt,
        'receiver_id' => $receiverId
    ]);

    // Optionally, you can return a success message or response
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Message sent successfully.'
    ]);
}

public function petture(){
    $file = $this->request->getFile('photo');
    $locationName = $this->request->getPost('locationName');
    $latitude = $this->request->getPost('latitude');
    $longitude = $this->request->getPost('longitude');

    if ($file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName); // Ensure this directory exists and is writable

        // Initialize the model and save the data
        $this->petLocations->save([
            'longitude' => $longitude,
            'latitude' => $latitude,
            'pet_picture' => $newName, // Save the file name of the uploaded picture
            'address' => $locationName, // Assuming the 'address' field is for the location name
        ]);

        return $this->respondCreated([
            'message' => 'File and location data saved successfully',
            'data' => [
                'locationName' => $locationName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'filePath' => WRITEPATH . 'uploads/' . $newName
            ]
        ]);
    }

    return $this->fail('Upload failed', ResponseInterface::HTTP_BAD_REQUEST);
}
}
