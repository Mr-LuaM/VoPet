<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetLocationsModel;
use App\Models\TransactionModel;
use CodeIgniter\HTTP\ResponseInterface;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Request;
use CodeIgniter\API\ResponseTrait;
use App\Traits\SendVerificationEmail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends ResourceController
{
    public function monthlyAdoptions()
    {
        $model = new TransactionModel();
    
        // Retrieve the 'year' query parameter from the request, use current year as default
        $requestedYear = $this->request->getGet('year') ?? date('Y');
    
        $data = $model->select("MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count")
                      ->where('status', 'completed')
                      ->where('YEAR(created_at)', $requestedYear)
                      ->groupBy("YEAR(created_at), MONTH(created_at)")
                      ->findAll();
    
        // Initialize response structure with all months set to 0 counts
        $response = [
            'labels' => [],
            'data' => array_fill(0, 12, 0) // Initialize all months with 0
        ];
    
        // Populate labels with month names for the requested year
        for ($i = 1; $i <= 12; $i++) {
            $response['labels'][] = date("F", mktime(0, 0, 0, $i, 1)) . " {$requestedYear}";
        }
    
        // Update counts for months with data
        foreach ($data as $row) {
            $monthIndex = (int)$row['month'] - 1; // Adjust for 0-indexed array
            $response['data'][$monthIndex] = (int)$row['count'];
        }
    
        return $this->respond($response);
    }
    
    
    public function monthlyRescues()
    {
        $model = new PetLocationsModel();
        
        // Retrieve the 'year' query parameter from the request. Default to the current year if not provided.
        $requestedYear = $this->request->getGet('year') ?? date('Y');
    
        $data = $model->select("MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count")
                      ->where('is_rescued', 1)
                      ->where('YEAR(created_at)', $requestedYear)
                      ->groupBy("YEAR(created_at), MONTH(created_at)")
                      ->findAll();
    
        // Initialize response structure with all months set to 0 counts
        $response = [
            'labels' => [],
            'data' => array_fill(0, 12, 0) // Initialize all months with 0
        ];
    
        // Populate labels with month names for the requested year
        for ($i = 1; $i <= 12; $i++) {
            $response['labels'][] = date("F", mktime(0, 0, 0, $i, 1, $requestedYear));
        }
    
        // Update counts for months with data
        foreach ($data as $row) {
            $monthIndex = (int)$row['month'] - 1; // Adjust for 0-indexed array
            $response['data'][$monthIndex] = (int)$row['count'];
        }
    
        return $this->respond($response);
    }
    

    private function formatResponseData($data, $type)
    {
        $response = ['labels' => [], 'data' => []];

        foreach ($data as $row) {
            $monthName = date("F", mktime(0, 0, 0, $row['month'], 10));
            $label = "{$monthName} {$row['year']}";
            $response['labels'][] = $label;
            $response['data'][] = (int)$row['count'];
        }

        return $response;
    }
}
