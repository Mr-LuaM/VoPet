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
    public function __construct(){   
        $this->pets = new \App\Models\PetModel();
    }
    public function getPets()
    {

        $pets = $this->pets->findAll(); // Assuming findAll() returns all pets from the database
        return $this->respond($pets); // Return the pets as JSON response
    }

}
