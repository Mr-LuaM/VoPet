<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Request;
use CodeIgniter\API\ResponseTrait;
use App\Traits\SendVerificationEmail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
;

class AuthController extends ResourceController
{
    use ResponseTrait;
    use SendVerificationEmail;
    private $users;
    public function __construct(){
        $this->users = new \App\Models\UserModel();
    }


    public function signup()
    {
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
     'message' => 'User registered successfully',
     'data' => $responseData
 ]);    }

 public function verifyOtp()
{
    $json = $this->request->getJSON();
    $email = $json->email ?? '';
    $otp = $json->otp ?? '';

    $user = $this->users->where('email', $email)->first();

    if (!$user) {
        return $this->failNotFound('User not found');
    }

    if ($user['email_confirmed']) {
       return $this->fail('Email is already verified.');
   }

    // Check for excessive verification attempts
    $minutesSinceLastAttempt = null; // Initialize the variable
    if ($user['verification_attempts'] >= 5) {
        $lastAttemptTime = new \DateTime($user['last_attempt_time']);
        $currentTime = new \DateTime();
        $diff = $currentTime->diff($lastAttemptTime);
        $minutesSinceLastAttempt = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    
        if ($minutesSinceLastAttempt < 10) {
            return $this->fail("Too many attempts. Please try again in " . (10 - $minutesSinceLastAttempt) . " minutes.");
        }
    }

    // Increment verification attempts and update last attempt time
    // Reset attempts if 10 minutes have passed since the last attempt
    $verificationAttempts = ($minutesSinceLastAttempt >= 10 || $minutesSinceLastAttempt === null) ? 1 : $user['verification_attempts'] + 1;    $this->users->update($user['user_id'], [
        'last_attempt_time' => date('Y-m-d H:i:s'),
        'verification_attempts' => $verificationAttempts,
    ]);

    // Check if OTP matches and is not expired
    $currentTime = new \DateTime();
    $tokenExpiresAt = new \DateTime($user['token_expires_at']);
    if ($user['verification_token'] == $otp && $currentTime < $tokenExpiresAt) {
        // Mark email as verified and reset verification attempts
        $this->users->update($user['user_id'], [
            'email_confirmed' => 1, 
            'verification_token' => null, 
            'token_expires_at' => null,
            'verification_attempts' => 0, // Reset attempts on successful verification
            'last_attempt_time' => null // Optionally reset last attempt time
        ]);

        return $this->respondUpdated(['message' => 'Email verified successfully']);
    } else {
        if($currentTime >= $tokenExpiresAt) {
            return $this->fail('OTP has expired');
        } else {
            return $this->fail('Invalid OTP');
        }
    }
}


 public function resendOtp()
    {
        $json = $this->request->getJSON();
        $email = $json->email ?? '';


        $user = $this->users->where('email', $email)->first();
        
        if (!$user) {
            return $this->failNotFound('User not found');
        }
        if ($user['email_confirmed']) {
            return $this->respond(['message' => 'Email is already verified. No need to resend OTP.']);
        }
        // Generate a new 6-digit verification token
        $verificationToken = rand(100000, 999999);
        $tokenExpiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Token expires in 10 minutes

        // Update user record with new token and expiration
        $this->users->update($user['user_id'], [
            'verification_token' => $verificationToken,
            'token_expires_at' => $tokenExpiresAt
        ]);

        // Send the OTP to the user's email
        // Customize the email subject and body
    $emailSubject = 'Resend OTP';
    $emailBody = 'Your new OTP is: ' . $verificationToken . '<br>Please use this code to verify your email address. This code will expire in 10 minutes.';

    // Send the OTP email with customized content
    $this->_sendVerificationEmail($email, $emailSubject, $emailBody);

        return $this->respondUpdated(['message' => 'OTP resent successfully.']);
    }
    public function test(){
        echo 'hi';
    }
    public function login()
{
    $json = $this->request->getJSON(true);
    $email = $json['email'] ?? '';
    $password = $json['password'] ?? '';

    $user = $this->users->where('email', $email)->first();

    // Check if user exists
    if (!$user) {
        return $this->failNotFound('User not found');
    }
        if (!$user['email_confirmed']) {
            return $this->failUnauthorized('Email not confirmed. Please verify your email before logging in.');
        }

    // Check if account is suspended
    if (isset($user['status']) && $user['status'] === 'suspended') {
        return $this->fail('This account has been suspended.', 403); // Or another appropriate status code
    }

    // Check if account is locked
    if (!empty($user['lock_until']) && new \DateTime() < new \DateTime($user['lock_until'])) {
        return $this->fail('Account is temporarily locked. Please try again later.', 403); // Or another appropriate status code
    }

    if (password_verify($password, $user['password'])) {
        // Reset failed attempts on successful login
        $this->users->update($user['user_id'], ['failed_login_attempts' => 0, 'lock_until' => null]);
    } else {
        // Handle failed login attempt
        $failedAttempts = $user['failed_login_attempts'] + 1;
        $lockUntil = null;

        if ($failedAttempts >= 7) {
            // Lock account for 3 hours
            $lockUntil = date('Y-m-d H:i:s', strtotime('+3 hours'));
            $failedAttempts = 0; // Consider whether to reset attempts here
        }

        $this->users->update($user['user_id'], [
            'failed_login_attempts' => $failedAttempts,
            'lock_until' => $lockUntil
        ]);

        return $this->failUnauthorized('Invalid email or password');
    }
  
    // JWT generation
    $key = getenv('JWT_SECRET');
    $payload = [
        'iat' => time(),
        'exp' => time() + 3600,
        'sub' => $user['user_id'],
    ];

    $jwt = JWT::encode($payload, $key, 'HS256');

    return $this->respond(['token' => $jwt, 'message' => 'Login successful'], 200);
}


    public function forgotPassword()
    {
        // Parse the JSON request body to get the email
        $data = $this->request->getJSON(true);
        $email = $data['email'];
    
        // Validate the email (you can add more validation here)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Invalid email format', 400);
        }
    
        $user = $this->users->where('email', $email)->first();
    
        if (!$user) {
            return $this->fail('User not found', 404);
        }
    
        // Generate a secure, unique token for password reset
        // This token should be safely hashed in the database
        $passwordResetToken = bin2hex(random_bytes(32));
        $passwordResetExpiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
        // Store the reset token in your database associated with the user's email
        // Ensure to safely hash the token before storing it
        $this->users->update($user['user_id'], [
            'password_reset_token' => password_hash($passwordResetToken, PASSWORD_DEFAULT),
            'password_reset_expires_at' => $passwordResetExpiresAt
        ]);
    
        // Construct the password reset link
        $passwordResetLink = "http://localhost:8081/reset-password?token=$passwordResetToken&email=$email";
    
        // Send an email to the user with the reset link
        $emailSubject = 'Password Reset Request';
        $emailBody = "You have requested to reset your password. Please click on the following link to set a new password: <a href=\"$passwordResetLink\">Reset Password</a><br>This link will expire in 1 hour.";
    
        $this->_sendVerificationEmail($email, $emailSubject, $emailBody);
    
        return $this->respond(['message' => 'Password reset instructions sent to your email. Please check your inbox.'], 200);
    }
    
    

    public function resetPassword()
    {
        $rules = [
            'token' => 'required',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
            'confirmPassword' => 'required|matches[password]',
        ];
    
        $data = $this->request->getJSON(true);
    
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }
    
        // Retrieve user by email
        $user = $this->users->where('email', $data['email'])->first();
    
        if (!$user) {
            return $this->failNotFound('Email is incorrect.');
        }
    
        // Verify the token using password_verify
        // Note: This assumes the 'password_reset_token' in the database is hashed
        if (!password_verify($data['token'], $user['password_reset_token'])) {
            return $this->failNotFound('Token is incorrect or has expired.');
        }
    
        // Check if token is expired
        if (time() > strtotime($user['password_reset_expires_at'])) {
            return $this->fail('Token has expired.');
        }
    
        // Hash the new password
        $newPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
        // Update user's password and reset the token
        $this->users->update($user['user_id'], [
            'password' => $newPassword,
            'password_reset_token' => null, // Clear the reset token
            'password_reset_expires_at' => null, // Clear the expiration time
        ]);
    
        return $this->respondUpdated(['message' => 'Password has been reset successfully.']);
    }
    public function userDetails()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = explode(' ', $authHeader)[1] ?? '';
      
        if (!$token) {
            return $this->failUnauthorized('Token required');
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));

$userId = $decoded->sub;


$personalInfoFields = 'email, fname, lname, birthdate, sex, contact_number, address, picture_url,role';

$user = $this->users->select($personalInfoFields)->find($userId);

            
            if (!$user) {
                return $this->failNotFound('User not found');
            }
            
            return $this->respond($user);
        } catch (\Exception $e) {
            log_message('error', 'JWT decoding failed: ' . $e->getMessage());
            return $this->failUnauthorized('Invalid token');
        }
    }
    
}
