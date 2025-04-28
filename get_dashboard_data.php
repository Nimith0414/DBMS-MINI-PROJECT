<?php
// Include database connection
require_once '../db.php';

// Set header to JSON
header('Content-Type: application/json');

try {
    // Enable detailed error output
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $dashboard_data = [];
    
    // Count total products
    $sql = "SELECT COUNT(*) as total_products FROM Product";
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $row = pg_fetch_assoc($result);
    $dashboard_data['total_products'] = (int)$row['total_products'];
    
    // Count total categories
    $sql = "SELECT COUNT(*) as total_categories FROM Category";
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $row = pg_fetch_assoc($result);
    $dashboard_data['total_categories'] = (int)$row['total_categories'];
    
    // Count total raw materials
    $sql = "SELECT COUNT(*) as total_materials FROM Raw_Material";
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $row = pg_fetch_assoc($result);
    $dashboard_data['total_materials'] = (int)$row['total_materials'];
    
    // Count low stock raw materials
    $sql = "SELECT COUNT(*) as low_stock_count FROM Raw_Material WHERE Stock_Quantity < Reorder_Level";
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $row = pg_fetch_assoc($result);
    $dashboard_data['low_stock_count'] = (int)$row['low_stock_count'];
    
    // Get low stock raw materials
    $sql = "SELECT r.*, s.Supplier_Name 
            FROM Raw_Material r
            LEFT JOIN Supplier s ON r.Supplier_ID = s.Supplier_ID
            WHERE r.Stock_Quantity < r.Reorder_Level
            ORDER BY r.Material_ID";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $low_stock_materials = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $low_stock_materials[] = [
            'Material_ID' => (int)$row['material_id'],
            'Material_Name' => $row['material_name'],
            'Supplier_ID' => (int)$row['supplier_id'],
            'Supplier_Name' => $row['supplier_name'],
            'Stock_Quantity' => (int)$row['stock_quantity'],
            'Unit_Price' => (float)$row['unit_price'],
            'Reorder_Level' => (int)$row['reorder_level']
        ];
    }
    
    $dashboard_data['low_stock_materials'] = $low_stock_materials;
    
    // Get product categories distribution
    $sql = "SELECT c.Category_Name, COUNT(*) as product_count 
            FROM Product p
            JOIN Category c ON p.Category_ID = c.Category_ID
            GROUP BY p.Category_ID, c.Category_Name
            ORDER BY product_count DESC";
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . pg_last_error($conn));
    }
    
    $category_distribution = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $category_distribution[] = [
            'category' => $row['category_name'],
            'count' => (int)$row['product_count']
        ];
    }
    
    $dashboard_data['category_distribution'] = $category_distribution;
    
    // Send response
    send_response(['success' => true, 'data' => $dashboard_data]);
    
} catch (Exception $e) {
    // Send error response
    send_response(['success' => false, 'message' => $e->getMessage()], 500);
}

// Close connection
pg_close($conn);
?>
