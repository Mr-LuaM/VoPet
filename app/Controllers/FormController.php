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


class FormController extends ResourceController
{
    private$pets;
    private $patientpet;

    private $clinics;
    protected $db;
    public function __construct(){   
        $this->pets = new \App\Models\PetModel();
        $this->patientpet = new \App\Models\PatientpetModel();
        $this->clinics = new \App\Models\ClinicDetails();
        $this->db = \Config\Database::connect();
    }
    public function getPets()
    {

        $pets = $this->pets->findAll(); // Assuming findAll() returns all pets from the database
        return $this->respond($pets); // Return the pets as JSON response
    }
public function getClientPets()
{
    $builder = $this->db->table('patient_pets');
    $builder->select('patient_pets.*, CONCAT(users.fname, " ", users.lname) AS user_name, users.user_id');
    $builder->join('users', 'users.user_id = patient_pets.owner_user_id');

    $pets = $builder->get()->getResultArray(); // Fetch the results as an array.

    if (empty($pets)) {
        return $this->failNotFound('No pets found');
    }

    // Optionally process the results to organize by owner or any other logic needed

    return $this->respond([
        'status' => 'success',
        'data' => $pets
    ]);
}

public function getClientUsers()
{
    $builder = $this->db->table('users');
    $builder->select('user_id, CONCAT(fname, " ", lname) AS user_name');
    $builder->where('role', 'user');

    $query = $builder->get();

    $users = $query->getResultArray();

    if (empty($users)) {
        return $this->failNotFound('No users found');
    }

    return $this->respond([
        'status' => 'success',
        'data' => $users
    ]);
}
public function clinics(){
    try {
        $clinics = $this->clinics->findAll(); // Fetch all clinics, adjust based on your needs

        // Format data for response
        $data = array_map(function ($clinic) {
            return [
                'id' => $clinic['clinic_id'], // Adjust based on your column name
                'name' => $clinic['clinic_name'], // Adjust based on your column name
            ];
        }, $clinics);

        return $this->respond([
            'status' => 200,
            'error' => null,
            'data' => $data
        ]);
    } catch (\Exception $e) {
        // Handle potential errors
        return $this->respond([
            'status' => 500,
            'error' => 'Failed to fetch clinics: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

    


}
