<?php
// Include database connection
require_once '../db.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // SQL query to get all products with category name
    $sql = "SELECT p.*, c.Category_Name 
            FROM Product p
            LEFT JOIN Category c ON p.Category_ID = c.Category_ID
            ORDER BY p.Product_ID";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $products = [];
    
    // Fetch all products
    while ($row = pg_fetch_assoc($result)) {
        $products[] = [
            'Product_ID' => (int)$row['product_id'],
            'Product_Name' => $row['product_name'],
            'Category_ID' => (int)$row['category_id'],
            'Category_Name' => $row['category_name'],
            'Manufacturing_Cost' => (float)$row['manufacturing_cost'],
            'Selling_Price' => (float)$row['selling_price'],
            'Profit_Margin' => (float)$row['selling_price'] - (float)$row['manufacturing_cost']
        ];
    }
    
    // Send response
    send_response(['success' => true, 'data' => $products]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
