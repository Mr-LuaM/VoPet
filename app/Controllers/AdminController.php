<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Request;
use CodeIgniter\API\ResponseTrait;
use Exception;
use App\Traits\SendVerificationEmail;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class AdminController extends BaseController
{
    use SendVerificationEmail;
    use ResponseTrait;

    protected $firebaseServerKey = 'AAAAGdrwYnc:APA91bGXw5cv3F3unpb56ySKuWglwbUrCNnYbsyZ7RxDCHLTHSIapFcRmcNoOpWIYIBY928el1PEN39VOyK_kqu1T13Pq78GqvrTg8T8EDmAxFY2Iv0S_B9E9Jf-TDL2RTaSZyXlx-IX';

    private $users;
    private $announcements;

    private $officeContacts;

    private $pets;

    private $transactions;
    private $medicalHistory;
    private $messages;

    private $petLocations;

    private $patientpet;
    protected $db;

    protected $clinic;
    public function __construct()
    {
        $this->users = new \App\Models\UserModel();
        $this->announcements = new \App\Models\AnnouncementModel();
        $this->officeContacts = new \App\Models\OfficeContactsModel();
        $this->pets = new \App\Models\PetModel();
        $this->transactions = new \App\Models\TransactionModel();
        $this->medicalHistory = new \App\Models\MedicalHistoryModel();
        $this->messages = new \App\Models\MessagesModel();
        $this->petLocations = new \App\Models\PetLocationsModel();
        $this->patientpet = new \App\Models\PatientPetModel();
        $this->clinic = new \App\Models\ClinicDetails();
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
            if ($role === 'admin' || $role === 'user' || $role === 'clinic') {
                // Get users with only the necessary details for user management, excluding the current user
                $users = $this->db->table('users as u')
                    ->select('u.user_id, u.email, u.role, u.fname, u.lname, u.status, u.last_login_at, u.last_login_ip, u.picture_url, c.clinic_id, c.clinic_name, c.created_at AS clinic_created_at, c.updated_at AS clinic_updated_at')
                    ->join('clinic_details as c', 'u.user_id = c.user_id', 'left')
                    ->get()
                    ->getResult();


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


    public function addUser()
    {

        $rules = [
            'fname' => 'required|min_length[2]',
            'lname' => 'required|min_length[2]',
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'This email is already registered. Please use another email.'
                ]
            ],
            'password' => 'required|min_length[8]',
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
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;

        // Check if clinic_id is not null
        if (isset($decoded->clinic_id)) {
            $clinicId = $decoded->clinic_id;
            // Adjust the query to include clinic_id filter
            $recentPets = $this->pets
                ->select('
                pets.pet_id,
                pets.name,
                pets.age,
                pets.species,
                pets.breed,
                pets.color,
                pets.status,
                pets.distinguishing_marks,
                pets.photo,
                pets.created_at,
                pets.updated_at,
                pets.gender,
                pets.clinic_id,
                clinic_details.clinic_name,
                clinic_details.clinic_id as clinic_detail_id')
                ->join('clinic_details', 'clinic_details.clinic_id = pets.clinic_id', 'left')
                ->where('pets.status', 'available')
                ->where('pets.clinic_id', $clinicId) // Filter by clinic_id
                ->orderBy('pets.created_at', 'desc')
                ->findAll();
        } else {
            // For admin role or other roles without clinic_id, fetch all available pets
            $recentPets = $this->pets
                ->select('
                pets.pet_id,
                pets.name,
                pets.age,
                pets.species,
                pets.breed,
                pets.color,
                pets.status,
                pets.distinguishing_marks,
                pets.photo,
                pets.created_at,
                pets.updated_at,
                pets.gender,
                pets.clinic_id,
                clinic_details.clinic_name,
                clinic_details.clinic_id as clinic_detail_id')
                ->join('clinic_details', 'clinic_details.clinic_id = pets.clinic_id', 'left')
                ->where('pets.status', 'available')
                ->orderBy('pets.created_at', 'desc')
                ->findAll();
        }

        return $this->respond($recentPets);
    }



    public function addPet()
    {
        helper(['form', 'url']);

        // Decode JWT token to get user information
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->sub;
        $clinicId = null;

        // Check if clinic ID is present in JWT payload
        if (isset($decoded->clinic_id)) {
            $clinicId = $decoded->clinic_id;
        } else {
            // Clinic ID not present in JWT payload, check form data
            $clinicId = $this->request->getVar('clinic_id');
            // Check if clinic ID is not a number or if it's empty, then set it to null
            $clinicId = is_numeric($clinicId) ? $clinicId : null;
        }

        // Adjust the validation for 'age' to accommodate '1y 3m' format and add 'color'
        $inputValidation = $this->validate([
            'name' => 'required',
            'age' => 'required', // Adjusted, consider custom validation if needed
            'species' => 'required',
            'breed' => 'required',
            'color' => 'required', // Added color validation
            'gender' => 'required',
            'distinguishing_marks' => 'required',
            'photo' => [
                'uploaded[photo]',
                'mime_in[photo,image/jpg,image/jpeg,image/gif,image/png]',
                'max_size[photo,4096]',
            ],
        ]);

        if (!$inputValidation) {
            return $this->fail($this->validator->getErrors());
        }

        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/pets', $newName);
            $photoPath = '/uploads/pets/' . $newName;
        } else {
            return $this->fail('Invalid photo upload');
        }

        // Include 'color' and use 'distinguishing_marks'
        $data = [
            'name' => $this->request->getVar('name'),
            'age' => $this->request->getVar('age'),
            'species' => $this->request->getVar('species'),
            'breed' => $this->request->getVar('breed'),
            'color' => $this->request->getVar('color'), // Added color
            'gender' => $this->request->getVar('gender'),
            'distinguishing_marks' => $this->request->getVar('distinguishing_marks'), // Updated
            'photo' => $newName, // Use the stored path
            'status' => 'available',
            'clinic_id' => $clinicId, // Set clinic_id based on the check
        ];

        if ($this->pets->insert($data)) {
            return $this->respondCreated(['message' => 'Pet added successfully'], 201);
        } else {
            return $this->failServerError('Failed to add pet');
        }
    }



    public function updatePet()
    {
        helper(['form', 'url']);

        // Define validation rules
        $rules = [
            'name' => 'required',
            'age' => 'required', // Consider custom validation rule for "1y 3m" format
            'species' => 'required',
            'breed' => 'required',
            'color' => 'required', // Added color validation
            'gender' => 'required',
            'distinguishing_marks' => 'required',
            'clinic_id' => 'permit_empty', // Adjusted from 'info' to 'distinguishing_marks'
            // Updated from 'info' to 'distinguishing_marks'
            // Consider conditional validation for 'photo' if needed
        ];

        // Validate input data
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Retrieve pet ID from request data
        $petId = $this->request->getPost('pet_id');
        if (!$petId) {
            return $this->fail('Pet ID is required for update', 400);
        }

        // Process photo upload if provided
        $file = $this->request->getFile('photo');
        $photoPath = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/pets', $newName);
            $photoPath = '/uploads/pets/' . $newName;
        }

        // Prepare data to update
        $dataToUpdate = [
            'name' => $this->request->getPost('name'),
            'age' => $this->request->getPost('age'),
            'species' => $this->request->getPost('species'),
            'breed' => $this->request->getPost('breed'),
            'color' => $this->request->getPost('color'), // Added color
            'gender' => $this->request->getPost('gender'),
            'distinguishing_marks' => $this->request->getPost('distinguishing_marks'), // Updated
        ];

        // Check if clinic ID is present in JWT payload or form data
        $clinicId = null;
        $decoded = JWT::decode(substr($this->request->getHeaderLine('Authorization'), 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        if (isset($decoded->clinic_id)) {
            $clinicId = $decoded->clinic_id;
        } else {
            $clinicId = $this->request->getVar('clinic_id');
            $clinicId = is_numeric($clinicId) ? $clinicId : null;
        }

        // Assign clinic ID to data if available
        $dataToUpdate['clinic_id'] = $clinicId;

        // Update photo path if uploaded
        if ($photoPath !== null) {
            $dataToUpdate['photo'] = $newName; // Use path for consistency
        }

        // Perform database update
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
            // Get the clinic ID from the JWT token
            $jwt = $this->request->getHeaderLine('Authorization');
            $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
            $clinicId = isset($decoded->clinic_id) ? $decoded->clinic_id : null;

            // Retrieve transactions
            $query = $this->transactions
                ->select('transactions.transaction_id, transactions.status, transactions.created_at, transactions.updated_at, 
                      pets.name AS pet_name, pets.age, pets.species, pets.breed, pets.status AS pet_status, pets.distinguishing_marks, pets.color, pets.photo, pets.gender,
                      users.fname, users.lname, users.email, users.contact_number, users.picture_url')
                ->join('pets', 'pets.pet_id = transactions.pet_id', 'left')
                ->join('users', 'users.user_id = transactions.user_id', 'left')
                ->where('transactions.status', 'requested');

            // Filter by clinic ID if available
            if ($clinicId !== null) {
                $query->where('pets.clinic_id', $clinicId);
            }

            $transactions = $query->findAll();

            return $this->respond([
                'status' => 200,
                'error' => null,
                'data' => $transactions
            ]);
        } catch (Exception $e) {
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
            $transactionId = isset($json->transaction_id) ? (int)$json->transaction_id : 0;
            $status = $json->status ?? '';

            $jwt = $this->request->getHeaderLine('Authorization');
            $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
            $loggedInUserId = $decoded->sub;

            $db->transStart();

            $transaction = $this->transactions->find($transactionId);
            if (!$transaction) {
                return $this->failNotFound('Transaction not found');
            }

            $this->transactions->update($transactionId, ['status' => $status]);

            $pet = $this->pets->find($transaction['pet_id']);
            if (!$pet) {
                return $this->failNotFound('Pet not found');
            }

            $receiverId = $transaction['user_id'];
            // Assuming 'users' is a model or a way to access user data
            $receiver = $this->users->find($receiverId);

            $messageData = [
                'sender_id' => $loggedInUserId,
                'receiver_id' => $receiverId,
                'content' => 'Adoption approved for pet ID: ' . $pet['pet_id'] . ' named ' . $pet['name'],
            ];
            $this->messages->insert($messageData);

            $messageData = [
                'sender_id' => $loggedInUserId,
                'receiver_id' => $receiverId,
                'content' => 'Please visit our office next week to claim',
            ];
            $this->messages->insert($messageData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Send email notification
            // After approving the transaction and updating database records
            $receiverEmail = $receiver['email']; // Assuming you have the user's email address
            $petName = $pet['name'];
            $subject = "Adoption Approval Notification";
            $body = "Dear User,<br><br>Your adoption application for <strong>$petName</strong> has been approved. Please visit our office next week to claim your new pet.<br><br>Best Regards,<br>Vopet";

            // Send the email
            $this->sendEmail($receiverEmail, $subject, $body);



            return $this->respondUpdated(['message' => 'Transaction approved']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Transaction approval error: ' . $e->getMessage());
            return $this->failServerError('An error occurred during the transaction approval process.');
        }
    }
    public function rejectTransaction()
    {
        $db = \Config\Database::connect(); // Get the database connection instance

        try {
            $json = $this->request->getJSON();
            $transactionId = isset($json->transaction_id) ? (int)$json->transaction_id : 0;
            $reason = $json->reason ?? 'No reason provided'; // Capture the rejection reason
            $status = 'denied'; // Assuming you're setting the status directly here

            if (!$transactionId) {
                return $this->failValidationError('Invalid transaction ID.');
            }

            $jwt = $this->request->getHeaderLine('Authorization');
            $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
            $loggedInUserId = $decoded->sub;
            $db->transStart(); // Begin transaction

            $transaction = $this->transactions->find($transactionId);
            if (!$transaction) {
                return $this->failNotFound('Transaction not found.');
            }

            $this->transactions->update($transactionId, ['status' => $status]);

            $petId = $transaction['pet_id'];
            $this->pets->update($petId, ['status' => 'available']);

            $pet = $this->pets->find($petId);
            $receiverId =  $transaction['user_id'];
            $receiver = $this->users->find($receiverId); // Assuming users model is available

            // Insert rejection notice
            $this->messages->insert([
                'sender_id' => $loggedInUserId,
                'receiver_id' => $receiverId,
                'content' => 'Adoption rejected for pet ID: ' . $pet['pet_id'] . ' named ' . $pet['name'],
            ]);

            // Insert rejection reason
            $this->messages->insert([
                'sender_id' => $loggedInUserId,
                'receiver_id' => $receiverId,
                'content' => 'Reason for rejection: ' . $reason,
            ]);

            $db->transComplete(); // Attempt to commit the transaction

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed to complete.');
            }

            // After rejecting the transaction and updating database records
            $receiverEmail = $receiver['email']; // Assuming you have the user's email address
            $petName = $pet['name'];
            $reason = $json->reason ?? 'No specific reason provided'; // Capture the rejection reason from the request
            $subject = "Adoption Rejection Notification";
            $body = "Dear User,<br><br>We regret to inform you that your adoption application for <strong>$petName</strong> has been rejected.<br><br>Reason for Rejection: $reason<br><br>Should you have any questions or require further clarification, please do not hesitate to contact us.<br><br>Best Regards,<br>Vopet";

            // Send the email
            $this->sendEmail($receiverEmail, $subject, $body);


            return $this->respondUpdated(['message' => 'Transaction denied and pet status updated to available.']);
        } catch (\Exception $e) {
            $db->transRollback(); // Ensure rollback on error
            log_message('error', 'Rejection error: ' . $e->getMessage());
            return $this->failServerError('An error occurred during the rejection process.');
        }
    }

    function sendEmail($to, $subject, $body, $isHTML = true, $altBody = '')
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'alluasan599@gmail.com';
            $mail->Password = 'oxht neem udqo pvgn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('alluasan599@gmail.com', 'Mailer');

            $mail->addAddress($to); // Add a recipient

            // Content
            $mail->isHTML($isHTML); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            if (!$isHTML) {
                $mail->AltBody = $altBody; // Optional: For non-HTML mail clients
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    public function getTransactionsHistory()
    {
        try {
            // Check for the authorization token and decode it to get the clinic ID
            $jwt = $this->request->getHeaderLine('Authorization');
            $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
            $clinicId = isset($decoded->clinic_id) ? $decoded->clinic_id : null;

            // Define the statuses you want to include in the history
            $statuses = ['approved', 'denied', 'unclaimed', 'completed'];

            // Query transactions based on clinic ID if available, otherwise fetch all transactions
            $transactionsQuery = $this->transactions
                ->select('transactions.transaction_id, transactions.status, transactions.created_at, transactions.updated_at, 
                      pets.name AS pet_name, pets.age, pets.species, pets.breed, pets.color, pets.status AS pet_status, 
                      pets.distinguishing_marks, pets.photo, pets.gender,
                      users.fname, users.lname, users.email, users.contact_number, users.picture_url')
                ->join('pets', 'pets.pet_id = transactions.pet_id', 'left')
                ->join('users', 'users.user_id = transactions.user_id', 'left');

            // Filter transactions by clinic ID if available
            if ($clinicId !== null) {
                $transactionsQuery->where('pets.clinic_id', $clinicId);
            }

            $transactionsQuery->whereIn('transactions.status', $statuses)
                ->orderBy('transactions.created_at', 'DESC'); // Ensure transactions are sorted by creation date in descending order

            // Execute the query
            $transactions = $transactionsQuery->findAll();

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
    public function userLocations()
    {
        $builder = $this->db->table('users');

        // Adjust the selection to extract and format the municipality, province, and region
        $builder->select("CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(address, ', ', -3), ', ', 1), ', ', SUBSTRING_INDEX(SUBSTRING_INDEX(address, ', ', -4), ', ', 1)) AS location, COUNT(DISTINCT transactions.pet_id) AS pet_count");

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
        // Load Firebase configurations
        $serviceAccountPath = FCPATH . 'pvKey.json';
        $googleProjectId = 'push-notifcation-99402'; // Replace with your Firebase Project ID
        $webPushLink = 'https://chalsim.online'; // Replace with your web app link

        // Ensure the service account key file exists
        if (!file_exists($serviceAccountPath)) {
            return [
                'success' => false,
                'error' => 'Service Account Key file not found.'
            ];
        }

        // Create credentials for Google API using the service account file
        $credential = new ServiceAccountCredentials(
            "https://www.googleapis.com/auth/firebase.messaging",
            json_decode(file_get_contents($serviceAccountPath), true)
        );

        // Get the OAuth2 access token
        $tokenData = $credential->fetchAuthToken(HttpHandlerFactory::build());
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to retrieve access token for Firebase.'
            ];
        }

        // FCM API URL
        $url = "https://fcm.googleapis.com/v1/projects/{$googleProjectId}/messages:send";

        $responses = [];

        // Loop through each token and send the notification
        foreach ($tokens as $token) {
            // Prepare the notification payload
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'webpush' => [
                        'fcm_options' => [
                            'link' => $webPushLink,
                        ]
                    ]
                ]
            ];

            // Initialize cURL and send the notification
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);

            if ($result === FALSE) {
                $responses[] = [
                    'token' => $token,
                    'success' => false,
                    'error' => curl_error($ch),
                ];
            } else {
                $responses[] = [
                    'token' => $token,
                    'success' => true,
                    'result' => json_decode($result, true),
                ];
            }

            curl_close($ch);
        }

        return $responses;
    }

    public function getMedicalHistory()
    {


        // Query for adopted pets' medical histories including transaction data for owner information
        $adoptedPetsHistory = $this->medicalHistory
            ->select('medical_history.*, pets.name as pet_name, pets.age, pets.species, pets.breed, pets.photo, transactions.user_id as owner_id, CONCAT(users.fname, " ", users.lname) AS owner_full_name')
            ->join('pets', 'pets.pet_id = medical_history.pet_id', 'left')
            ->join('transactions', 'transactions.pet_id = medical_history.pet_id', 'left')
            ->join('users', 'users.user_id = transactions.user_id', 'left')
            ->where('medical_history.is_correct', 1)
            ->where('medical_history.pet_id IS NOT NULL')
            ->findAll();



        return $this->respond(['status' => 'success', 'data' => $adoptedPetsHistory]);
    }

    public function markTransactionAsNotCorrect()
    {
        (int) $id = $this->request->getVar('id');
        $isCorrect = $this->request->getVar('is_correct');

        // Load the model

        // Check if the transaction exists
        $transaction = $this->medicalHistory->find($id);

        if (!$transaction) {
            return $this->failNotFound('Transaction not found.');
        }

        // Update the is_correct field
        $data = ['is_correct' => $isCorrect];
        $this->medicalHistory->update($id, $data);

        // Respond with a success message
        return $this->respond(['message' => 'Transaction marked as not correct.']);
    }

    public function addMedicalHistory()
    {
        $requestData = $this->request->getJSON(); // Get JSON data from request
        // Validate the data if necessary

        // Process the data and store it in the database
        // Example: Assuming you have a MedicalHistory model
        $medicalHistoryModel = new \App\Models\MedicalHistoryModel();
        $medicalHistoryModel->insert($requestData);

        // Return a success response
        return $this->response->setStatusCode(201)->setJSON(['status' => 'success', 'message' => 'Medical history added successfully']);
    }
    public function messages()
    {
        // Include user id in the announcement data
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $loggedInUserId = $decoded->sub;

        // Get the receiver_id from the request body
        $receiverId = $this->request->getVar('receiver_id');

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
        $jwt = $this->request->getHeaderLine('Authorization');
        $decoded = JWT::decode(substr($jwt, 7), new Key(getenv('JWT_SECRET'), 'HS256'));
        $loggedInUserId = $decoded->sub;

        $content = $this->request->getVar('content');
        $receiverId = $this->request->getVar('receiver_id');

        if ($content === '@generate adoption Agreement') {
            if ($this->checkUserRoleAndPendingTransactions($receiverId)) {
                $emailSent = $this->generateAndSendAdoptionAgreement($receiverId);
                if ($emailSent) {
                    $followUpContent = 'Adoption Agreement sent to email';
                    $this->messages->insert([
                        'content' => $followUpContent,
                        'sender_id' => $loggedInUserId, // Assuming this is the system or admin user ID
                        'receiver_id' => $receiverId,
                    ]);
                    $followUpContent = 'Please check your email';
                    $this->messages->insert([
                        'content' => $followUpContent,
                        'sender_id' => $loggedInUserId, // Assuming this is the system or admin user ID
                        'receiver_id' => $receiverId,
                    ]);
                    return $this->respondCreated(['message' => $followUpContent]);
                } else {
                    return $this->failServerError('Failed to send the adoption agreement. Please contact support.');
                }
            } else {
                return $this->fail('Operation not permitted. Either the receiver is not a user or there are no pending adoptions.', 403);
            }
        }

        $inserted = $this->messages->insert([
            'content' => $content,
            'sender_id' => $loggedInUserId,
            'receiver_id' => $receiverId
        ]);

        if ($inserted) {
            $notificationTitle = 'New Message from Vet Clinic';
            $notificationBody =  $content;
            $this->sendPushNotificationToUser($receiverId, $notificationTitle, $notificationBody);
            return $this->respondCreated(['success' => true, 'message' => 'Message sent successfully.']);
        } else {
            return $this->failServerError('Failed to send message');
        }
    }

    private function generateAndSendAdoptionAgreement($receiverId)
    {
        $userInfo = $this->users->find((int)$receiverId);
        if ($userInfo['role'] !== 'user') {
            return false; // Return false if the receiver is not a user
        }

        $pendingAdoptions = $this->transactions->where('user_id', (int)$receiverId)
            ->where('status', 'requested')
            ->findAll();

        $pets = [];
        foreach ($pendingAdoptions as $adoption) {
            $pets[] = $this->pets->find($adoption['pet_id']);
        }

        $pdfFilePath = $this->createAdoptionAgreementContent($pets);

        return $this->sendEmailWithAttachment($userInfo['email'], "Adoption Agreement", "Please find attached the adoption agreement.", $pdfFilePath);
    }

    function sendEmailWithAttachment($to, $subject, $body, $attachmentPath = '', $isHTML = true, $altBody = '')
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'alluasan599@gmail.com';
            $mail->Password = 'oxht neem udqo pvgn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            // Recipients
            $mail->setFrom('alluasan599@gmail.com', 'Mailer');
            $mail->addAddress($to); // Add a recipient

            // Content
            $mail->isHTML($isHTML); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            if (!$isHTML) {
                $mail->AltBody = $altBody; // Optional: For non-HTML mail clients
            }

            // Attachment
            if (!empty($attachmentPath) && file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath); // Attach PDF if exists
            } else {
                log_message('error', "PDF attachment missing or not readable: $attachmentPath");
                // Optionally, handle the error appropriately
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }


    private function createAdoptionAgreementContent($pets)
    {
        // Initialize HTML content with basic styling
        $htmlContent = "<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        h1, h2 { color: #333; }
        p, ul { font-size: 14px; }
        .pet-details, .adopter-responsibilities { margin-bottom: 20px; }
        .signature-area { margin-top: 40px; }
        .signature { font-family: 'Reenie Beanie', cursive; font-size: 24px; }
        .line { border-bottom: 1px solid #bbb; width: 300px; }
        .signatory { margin-top: 5px; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Adoption Agreement</h1>
    <p>This agreement confirms the adoption of the following pet(s) under the terms and conditions agreed upon by both the adopter and the adoption agency.</p>";

        // Iterate over each pet to include their details
        foreach ($pets as $pet) {
            $htmlContent .= "<div class='pet-details'><b>Pet Name:</b> " . htmlspecialchars($pet['name']) . "<br>"
                . "<b>Species:</b> " . htmlspecialchars($pet['species']) . "<br>"
                . "<b>Breed:</b> " . htmlspecialchars($pet['breed']) . "<br>"
                . "<b>Age:</b> " . htmlspecialchars($pet['age']) . "<br>"
                . "<b>Gender:</b> " . htmlspecialchars($pet['gender']) . "<br>"
                . "<b>Color:</b> " . htmlspecialchars($pet['color']) . "<br>"
                . "<b>Distinguishing Marks:</b> " . htmlspecialchars($pet['distinguishing_marks']) . "</div>";
        }

        // Include adopter's responsibilities
        $htmlContent .= "<div class='adopter-responsibilities'><h2>Adopter's Responsibilities:</h2><ul>"
            . "<li>Provide a loving and safe home for the pet(s).</li>"
            . "<li>Ensure the pet(s) receive regular veterinary care.</li>"
            . "<li>Comply with all local pet ownership laws and regulations.</li></ul>"
            . "<p>This agreement is binding and confirms the adopter's commitment to the care and welfare of the adopted pet(s).</p></div>";

        // Add signature area
        $htmlContent .= "<div class='signature-area'>
                    <div class='line'></div>
                    <div class='signatory'>Adopter's Signature</div>
                    <div class='signature' contenteditable='true'>[Signature]</div>
                    <div>Date: <span>" . date("Y-m-d") . "</span></div>
                </div>
                <div class='signature-area'>
                    <div class='line'></div>
                    <div class='signatory'>Agency Representative Signature</div>
                    <div class='signature' contenteditable='true'>[Signature]</div>
                    <div>Date: <span>" . date("Y-m-d") . "</span></div>
                </div>
</body>
</html>";


        // Initialize Dompdf
        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($htmlContent);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfDirectory = ROOTPATH . 'public/pdf'; // Adjust this path as needed.
            $pdfFileName = 'AdoptAgreement_' . time() . '.pdf'; // Using time() to avoid file name conflicts.
            $pdfFilePath = $pdfDirectory . $pdfFileName;

            if (!file_put_contents($pdfFilePath, $dompdf->output())) {
                // Handle failure to save PDF
                log_message('error', "Failed to save the generated PDF to: " . $pdfFilePath);
                return false;
            }
        } catch (Exception $e) {
            // Handle any exceptions during PDF generation
            log_message('error', "Exception while generating PDF: " . $e->getMessage());
            return false;
        }

        return $pdfFilePath;
    }


    private function checkUserRoleAndPendingTransactions($userId)
    {
        $userInfo = $this->users->find((int)$userId);
        if ($userInfo && $userInfo['role'] === 'user') {
            $pendingAdoptions = $this->transactions
                ->where('user_id', (int)$userId)
                ->where('status', 'requested')
                ->findAll();
            return !empty($pendingAdoptions);
        }
        return false;
    }



    public function sendPushNotificationToUser($receiverId, $title, $body)
    {
        // Fetch the FCM token of the receiver user
        $user = $this->users->find($receiverId); // Assuming `$this->users` is your user model instance
        if (empty($user['fcm_token'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User does not have a FCM token.'
            ]);
        }

        $token = $user['fcm_token'];

        // Load Firebase configurations
        $serviceAccountPath = FCPATH . 'pvKey.json';
        $googleProjectId = 'push-notifcation-99402'; // Replace with your Firebase Project ID
        $webPushLink = 'https://chalsim.online'; // Replace with your web app link

        // Ensure the service account key file exists
        if (!file_exists($serviceAccountPath)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Service Account Key file not found.'
            ]);
        }

        // Create credentials for Google API using the service account file
        $credential = new ServiceAccountCredentials(
            "https://www.googleapis.com/auth/firebase.messaging",
            json_decode(file_get_contents($serviceAccountPath), true)
        );

        // Get the OAuth2 access token
        $tokenData = $credential->fetchAuthToken(HttpHandlerFactory::build());
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to retrieve access token for Firebase.'
            ]);
        }

        // FCM API URL
        $url = "https://fcm.googleapis.com/v1/projects/{$googleProjectId}/messages:send";

        // Prepare the notification payload
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'webpush' => [
                    'fcm_options' => [
                        'link' => $webPushLink
                    ]
                ]
            ]
        ];

        // Initialize cURL and send the notification
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);

        if ($result === FALSE) {
            $response = [
                'success' => false,
                'error' => curl_error($ch),
            ];
        } else {
            $response = json_decode($result, true);
        }

        curl_close($ch);

        return $this->response->setJSON($response);
    }



    public function getPetLocations()
    {


        $data = $this->petLocations->orderBy('created_at', 'desc')->findAll();


        return $this->respond($data);
    }

    public function markAsRescued()
    {
        $json = $this->request->getJSON();
        if (!isset($json->location_id) || !isset($json->is_rescued)) {
            return $this->failNotFound('Missing required parameters');
        }

        $locationId = $json->location_id;
        $isRescued = filter_var($json->is_rescued, FILTER_VALIDATE_BOOLEAN);


        $updateData = ['is_rescued' => $isRescued];

        $updated = $this->petLocations->update($locationId, $updateData);

        if ($updated) {
            return $this->respondUpdated(['message' => 'Pet marked as rescued successfully']);
        } else {
            return $this->failServerError('Could not update the pet status');
        }
    }



    public function getPatientPetsMedicalHistory()
    {
        $patientPetsHistory = $this->db->table('medical_history') // Assuming this is the correct way to reference your medical_history model or table
            ->select('medical_history.*, patient_pets.name as pet_name, patient_pets.age, patient_pets.species, patient_pets.breed, patient_pets.photo, CONCAT(users.fname, " ", users.lname) AS owner_full_name')
            ->join('patient_pets', 'patient_pets.patient_pet_id = medical_history.patient_pet_id', 'left') // Ensure this join is correct
            ->join('users', 'users.user_id = patient_pets.owner_user_id', 'left')
            ->where('medical_history.is_correct', 1)
            ->where('medical_history.patient_pet_id IS NOT NULL')
            ->get()->getResultArray();

        return $this->respond(['status' => 'success', 'data' => $patientPetsHistory]);
    }


    public function addMedicalHistoryForExistingPets()
    {
        $json = $this->request->getJSON(true);

        // Initialize your models
        $patientPetsModel = $this->patientpet;
        $medicalHistoryModel = $this->medicalHistory;

        if (is_array($json['selectedPetId'])) {
            // Existing pet
            $patientPetId = $json['selectedPetId']['pet_id'];
        } else {
            // New pet
            $newPet = [
                'name' => $json['selectedPetId'], // Assuming 'selectedPetId' is a string for new pets
                'owner_user_id' => $json['selectedUserId'],
                // Add other default fields or fields derived from the request
            ];
            if (!$patientPetsModel->insert($newPet)) {
                return $this->fail('Failed to create new pet record');
            }

            $patientPetId = $patientPetsModel->getInsertID();
        }

        // Prepare medical history data
        $medicalHistoryData = [
            'pet_id' => $json['selectedPetId']['pet_id'] ?? null, // Uncomment if neededyy
            'patient_pet_id' => $patientPetId,
            'medical_condition' => $json['medical_condition'],
            'medication' => $json['medication'],
            'dosage' => $json['dosage'],
            'vaccination_type' => $json['vaccination_type'],
            'vaccination_date' => $json['vaccination_date'],
            'next_vaccination_date' => $json['next_vaccination_date'],
            'surgical_procedure' => $json['surgical_procedure'],
            'weight' => $json['weight'],
            'temperature' => $json['temperature'],
            'heart_rate' => $json['heart_rate'],
            'dietary_restrictions' => $json['dietary_restrictions'],
            'behavioral_notes' => $json['behavioral_notes'],
            'is_correct' => 1, // Assuming a default value
        ];

        if (!$medicalHistoryModel->insert($medicalHistoryData)) {
            return $this->fail('Failed to add medical history');
        }

        return $this->respondCreated('Medical history added successfully');
    }

    public function addNewClinic()
    {
        // Validation rules for user input
        $rules = [
            'name' => 'required|min_length[2]', // Clinic name
            'address' => 'required|min_length[5]', // Clinic address
            'fname' => 'required|min_length[2]',
            'lname' => 'required|min_length[2]',
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'This email is already registered. Please use another email.'
                ]
            ],
            'password' => 'required|min_length[8]',
            'birthdate' => 'required',
            'sex' => 'required',
            'contact_number' => 'required|min_length[10]',
            // Additional rules for clinic information if necessary
        ];

        $data = $this->request->getJSON(true);

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Hash password before saving
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role'] = 'clinic'; // or 'user', depending on your roles setup

        // Since it's done by admin, auto-validate the user
        $data['email_confirmed'] = 1;

        // Attempt to save user information
        if (!$userId = $this->users->insert($data)) {
            return $this->fail('Could not create user.');
        }
        $userId = $this->users->where('email', $data['email'])->first();

        // Prepare clinic data
        $clinicData = [
            'clinic_name' => $data['name'],
            'user_id' => (int)$userId['user_id'], // Associate the clinic with the newly created user
        ];

        // Attempt to save clinic information
        $this->clinic->insert($clinicData);


        // Success response
        return $this->respondCreated([
            'message' => 'Clinic and associated user created successfully',

        ]);
    }
    public function testSendPushNotificationToUser()
    {
        $receiverId = 31; // Static user ID for testing
        $title = 'Test Notification to User';
        $body = 'This is a test notification sent to a specific user.';

        $response = $this->sendPushNotificationToUser($receiverId, $title, $body);

        return $this->response->setJSON($response);
    }

    // Test `sendPushNotification`
    public function testSendPushNotification()
    {
        $tokens = [
            'cc4Tney0d7wWtryghUr3qo:APA91bGSj7-YgJDIFFNNKnhA5vlGDWR-TZQzPbtJj6hjb07KgFmoNwpxvJYEOoCCJ75vlzJ0xeZixy38CHKY9Qemec6fQVif2oy3DRIhrul3l6rh7WMyx_w',
            'cy-ZYpQW-Gopj4RDSN_Sh1:APA91bHGIvbz8Wfg6v90OAeKahe14ztynjms-gJooA44F-Sqi-xpAoXW5oHcmzil7rrUjzEuemenzXLRx728EEQOtUDT_7GVuDXMt5VkXCHkPxYBzqSWxMq-lG1bhLRyZq7voA9xKN-F'
        ];
        $title = 'Test Notification to Tokens';
        $body = 'This is a test notification sent to multiple tokens.';

        $response = $this->sendPushNotification($tokens, $title, $body);

        return $this->response->setJSON($response);
    }
}
