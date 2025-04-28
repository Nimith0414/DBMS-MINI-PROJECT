<?php
// Include database connection
require_once '../db.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // SQL query to get all suppliers
    $sql = "SELECT * FROM Supplier ORDER BY Supplier_ID";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $suppliers = [];
    
    // Fetch all suppliers
    while ($row = pg_fetch_assoc($result)) {
        $suppliers[] = [
            'Supplier_ID' => (int)$row['supplier_id'],
            'Supplier_Name' => $row['supplier_name'],
            'Contact_Info' => $row['contact_info'],
            'Address' => $row['address']
        ];
    }
    
    // Send response
    send_response(['success' => true, 'data' => $suppliers]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
