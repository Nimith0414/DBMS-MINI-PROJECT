<?php
// Include database connection
require_once '../db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(['success' => false, 'message' => 'Only POST requests are allowed'], 405);
}

try {
    // Get JSON data from request body
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Check if data is valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data");
    }
    
    // Validate required fields
    if (empty($data['Product_Name']) || 
        !isset($data['Category_ID']) || 
        !isset($data['Manufacturing_Cost']) || 
        !isset($data['Selling_Price'])) {
        throw new Exception("Missing required fields");
    }
    
    // Sanitize input data
    $product_name = sanitize_input($conn, $data['Product_Name']);
    $category_id = (int)$data['Category_ID'];
    $manufacturing_cost = (float)$data['Manufacturing_Cost'];
    $selling_price = (float)$data['Selling_Price'];
    
    // Additional validation
    if ($manufacturing_cost <= 0) {
        throw new Exception("Manufacturing cost must be greater than zero");
    }
    
    if ($selling_price <= 0) {
        throw new Exception("Selling price must be greater than zero");
    }
    
    // Prepare SQL statement for PostgreSQL
    $sql = "INSERT INTO Product (Product_Name, Category_ID, Manufacturing_Cost, Selling_Price) 
            VALUES ($1, $2, $3, $4) RETURNING Product_ID";
    
    $result = pg_query_params($conn, $sql, [$product_name, $category_id, $manufacturing_cost, $selling_price]);
    
    if (!$result) {
        throw new Exception("Error executing statement: " . pg_last_error($conn));
    }
    
    // Get the new product ID
    $row = pg_fetch_assoc($result);
    $new_product_id = $row['product_id'];
    
    // Get the newly added product with category name
    $sql = "SELECT p.*, c.Category_Name 
            FROM Product p
            LEFT JOIN Category c ON p.Category_ID = c.Category_ID
            WHERE p.Product_ID = $1";
    
    $result = pg_query_params($conn, $sql, [$new_product_id]);
    
    if (!$result) {
        throw new Exception("Error executing statement: " . pg_last_error($conn));
    }
    
    $new_product = pg_fetch_assoc($result);
    
    if (!$new_product) {
        throw new Exception("Error retrieving newly added product");
    }
    
    // Format product data
    $product_data = [
        'Product_ID' => (int)$new_product['product_id'],
        'Product_Name' => $new_product['product_name'],
        'Category_ID' => (int)$new_product['category_id'],
        'Category_Name' => $new_product['category_name'],
        'Manufacturing_Cost' => (float)$new_product['manufacturing_cost'],
        'Selling_Price' => (float)$new_product['selling_price'],
        'Profit_Margin' => (float)$new_product['selling_price'] - (float)$new_product['manufacturing_cost']
    ];
    
    // Send successful response
    send_response(['success' => true, 'message' => 'Product added successfully', 'data' => $product_data]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
