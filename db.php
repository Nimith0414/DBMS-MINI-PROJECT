<?php
// Database configuration using environment variables for PostgreSQL
$db_url = getenv("DATABASE_URL");
$host = getenv("PGHOST");
$port = getenv("PGPORT");
$username = getenv("PGUSER");
$password = getenv("PGPASSWORD");
$database = getenv("PGDATABASE");

// Create PostgreSQL database connection
$conn = pg_connect("host=$host port=$port dbname=$database user=$username password=$password");

// Check connection
if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . pg_last_error()]));
}

// Function to sanitize input data
function sanitize_input($conn, $data) {
    return pg_escape_string($conn, trim($data));
}

// Function to handle API response
function send_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Enable CORS for API endpoints
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
