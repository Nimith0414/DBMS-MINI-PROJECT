# Inventory Management System

A web-based inventory management system that allows users to manage products, raw materials, and suppliers.

## Features

- Dashboard with summary information and charts
- Product management
- Raw material tracking with low stock alerts
- Supplier information
- Stock update functionality

## Technical Details

### Database Structure

- PostgreSQL database
- Tables:
  - Supplier: Contains supplier information
  - Raw_Material: Tracks raw materials, quantities, and reorder levels
  - Category: Lists product categories
  - Product: Contains product information

### Back-end

- PHP APIs for data operations
- CRUD functionality for products and raw materials
- Stock management for materials
- PostgreSQL database connectivity

### Front-end

- Responsive design using Bootstrap
- Dynamic content loading with JavaScript fetch API
- Data visualization with Chart.js
- Form input validation

## Setup Instructions

1. Import the database schema from `project_db.sql`
2. Environment variables for database are automatically configured
3. Run the PHP server with `php -S 0.0.0.0:5000`
4. Access the application through a web browser at the root URL

## API Endpoints

- `get_products.php`: Fetch all products
- `get_raw_materials.php`: Fetch all raw materials
- `add_product.php`: Add a new product
- `update_stock.php`: Update stock level of raw material
- `get_suppliers.php`: Fetch supplier list
- `get_categories.php`: Fetch category list
- `add_raw_material.php`: Add a new raw material
- `get_dashboard_data.php`: Get dashboard summary data

## Usage

- Use the navigation sidebar to switch between different sections
- Add new products and raw materials using the corresponding forms
- Update stock levels of raw materials as needed
- Monitor low stock materials through the dashboard

## Development Notes

This application was originally designed for MySQL but has been adapted to work with PostgreSQL. Key differences include:
- Using PostgreSQL-specific connection methods (`pg_connect`, `pg_query_params`, etc.)
- Column names in PostgreSQL are lowercase by default
- PostgreSQL uses `$1`, `$2`, etc. for parameterized queries instead of `?`
- Sequences are used for auto-incrementing IDs
