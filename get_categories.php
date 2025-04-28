<?php
// Include database connection
require_once '../db.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // SQL query to get all categories
    $sql = "SELECT * FROM Category ORDER BY Category_ID";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $categories = [];
    
    // Fetch all categories
    while ($row = pg_fetch_assoc($result)) {
        $categories[] = [
            'Category_ID' => (int)$row['category_id'],
            'Category_Name' => $row['category_name'],
            'Description' => $row['description']
        ];
    }
    
    // Send response
    send_response(['success' => true, 'data' => $categories]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
