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
    if (empty($data['Material_Name']) || 
        !isset($data['Supplier_ID']) || 
        !isset($data['Stock_Quantity']) || 
        !isset($data['Unit_Price']) || 
        !isset($data['Reorder_Level'])) {
        throw new Exception("Missing required fields");
    }
    
    // Sanitize input data
    $material_name = sanitize_input($conn, $data['Material_Name']);
    $supplier_id = (int)$data['Supplier_ID'];
    $stock_quantity = (int)$data['Stock_Quantity'];
    $unit_price = (float)$data['Unit_Price'];
    $reorder_level = (int)$data['Reorder_Level'];
    
    // Additional validation
    if ($stock_quantity < 0) {
        throw new Exception("Stock quantity cannot be negative");
    }
    
    if ($unit_price <= 0) {
        throw new Exception("Unit price must be greater than zero");
    }
    
    if ($reorder_level < 0) {
        throw new Exception("Reorder level cannot be negative");
    }
    
    // Prepare SQL statement for PostgreSQL
    $sql = "INSERT INTO Raw_Material (Material_Name, Supplier_ID, Stock_Quantity, Unit_Price, Reorder_Level) 
            VALUES ($1, $2, $3, $4, $5) RETURNING Material_ID";
    
    $result = pg_query_params($conn, $sql, [$material_name, $supplier_id, $stock_quantity, $unit_price, $reorder_level]);
    
    if (!$result) {
        throw new Exception("Error executing statement: " . pg_last_error($conn));
    }
    
    // Get the new material ID
    $row = pg_fetch_assoc($result);
    $new_material_id = $row['material_id'];
    
    // Get the newly added material with supplier name
    $sql = "SELECT r.*, s.Supplier_Name 
            FROM Raw_Material r
            LEFT JOIN Supplier s ON r.Supplier_ID = s.Supplier_ID
            WHERE r.Material_ID = $1";
    
    $result = pg_query_params($conn, $sql, [$new_material_id]);
    
    if (!$result) {
        throw new Exception("Error executing statement: " . pg_last_error($conn));
    }
    
    $new_material = pg_fetch_assoc($result);
    
    if (!$new_material) {
        throw new Exception("Error retrieving newly added material");
    }
    
    // Format material data
    $material_data = [
        'Material_ID' => (int)$new_material['material_id'],
        'Material_Name' => $new_material['material_name'],
        'Supplier_ID' => (int)$new_material['supplier_id'],
        'Supplier_Name' => $new_material['supplier_name'],
        'Stock_Quantity' => (int)$new_material['stock_quantity'],
        'Unit_Price' => (float)$new_material['unit_price'],
        'Reorder_Level' => (int)$new_material['reorder_level'],
        'Low_Stock' => (int)$new_material['stock_quantity'] < (int)$new_material['reorder_level']
    ];
    
    // Send successful response
    send_response(['success' => true, 'message' => 'Raw material added successfully', 'data' => $material_data]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
