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
    if (!isset($data['Material_ID']) || !isset($data['Stock_Quantity'])) {
        throw new Exception("Missing required fields");
    }
    
    // Sanitize input data
    $material_id = (int)$data['Material_ID'];
    $stock_quantity = (int)$data['Stock_Quantity'];
    
    // Additional validation
    if ($stock_quantity < 0) {
        throw new Exception("Stock quantity cannot be negative");
    }
    
    // Prepare SQL statement for PostgreSQL
    $sql = "UPDATE Raw_Material SET Stock_Quantity = $1 WHERE Material_ID = $2";
    
    $result = pg_query_params($conn, $sql, [$stock_quantity, $material_id]);
    
    if (!$result) {
        throw new Exception("Error executing statement: " . pg_last_error($conn));
    }
    
    // Check if any rows were affected
    $affected_rows = pg_affected_rows($result);
    if ($affected_rows === 0) {
        throw new Exception("No material found with ID: " . $material_id);
    }
    
    // Get the updated material with supplier name
    $sql = "SELECT r.*, s.Supplier_Name 
            FROM Raw_Material r
            LEFT JOIN Supplier s ON r.Supplier_ID = s.Supplier_ID
            WHERE r.Material_ID = $1";
    
    $result = pg_query_params($conn, $sql, [$material_id]);
    
    if (!$result) {
        throw new Exception("Error executing statement: " . pg_last_error($conn));
    }
    
    $updated_material = pg_fetch_assoc($result);
    
    if (!$updated_material) {
        throw new Exception("Error retrieving updated material");
    }
    
    // Format material data
    $material_data = [
        'Material_ID' => (int)$updated_material['material_id'],
        'Material_Name' => $updated_material['material_name'],
        'Supplier_ID' => (int)$updated_material['supplier_id'],
        'Supplier_Name' => $updated_material['supplier_name'],
        'Stock_Quantity' => (int)$updated_material['stock_quantity'],
        'Unit_Price' => (float)$updated_material['unit_price'],
        'Reorder_Level' => (int)$updated_material['reorder_level'],
        'Low_Stock' => (int)$updated_material['stock_quantity'] < (int)$updated_material['reorder_level']
    ];
    
    // Send successful response
    send_response(['success' => true, 'message' => 'Stock updated successfully', 'data' => $material_data]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
