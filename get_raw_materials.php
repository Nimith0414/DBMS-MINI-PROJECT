<?php
// Include database connection
require_once '../db.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // SQL query to get all raw materials with supplier name
    $sql = "SELECT r.*, s.Supplier_Name 
            FROM Raw_Material r
            LEFT JOIN Supplier s ON r.Supplier_ID = s.Supplier_ID
            ORDER BY r.Material_ID";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $materials = [];
    
    // Fetch all raw materials
    while ($row = pg_fetch_assoc($result)) {
        $materials[] = [
            'Material_ID' => (int)$row['material_id'],
            'Material_Name' => $row['material_name'],
            'Supplier_ID' => (int)$row['supplier_id'],
            'Supplier_Name' => $row['supplier_name'],
            'Stock_Quantity' => (int)$row['stock_quantity'],
            'Unit_Price' => (float)$row['unit_price'],
            'Reorder_Level' => (int)$row['reorder_level'],
            'Low_Stock' => (int)$row['stock_quantity'] < (int)$row['reorder_level']
        ];
    }
    
    // Send response
    send_response(['success' => true, 'data' => $materials]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
