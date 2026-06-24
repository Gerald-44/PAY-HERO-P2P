<?php
include 'config.php';
// Allow cross-origin requests if needed
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get the reference from query parameter
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';

if (empty($reference)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Reference parameter is required"
    ]);
    exit;
}

// Set authorization token
$basicAuthToken = $paymentConfig['basicAuthToken'];

// Initialize cURL
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://backend.payhero.co.ke/api/v2/transaction-status?reference=' . urlencode($reference),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: ' . $basicAuthToken
  ),
));

// Execute the cURL request
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

// Handle response
if ($error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "cURL Error: " . $error
    ]);
} else {
    // Pass through the response from the API
    http_response_code($httpCode);
    echo $response;
}