-- PostgreSQL schema for INVENTORY3

-- Create sequences for auto-incrementing IDs
CREATE SEQUENCE supplier_id_seq;
CREATE SEQUENCE category_id_seq;
CREATE SEQUENCE material_id_seq;
CREATE SEQUENCE product_id_seq;

-- Create Supplier table
CREATE TABLE IF NOT EXISTS Supplier (
    Supplier_ID INT DEFAULT nextval('supplier_id_seq') PRIMARY KEY,
    Supplier_Name VARCHAR(100) NOT NULL,
    Contact_Info VARCHAR(100) NOT NULL,
    Address VARCHAR(255) NOT NULL
);

-- Create Category table
CREATE TABLE IF NOT EXISTS Category (
    Category_ID INT DEFAULT nextval('category_id_seq') PRIMARY KEY,
    Category_Name VARCHAR(100) NOT NULL,
    Description TEXT
);

-- Create Raw_Material table
CREATE TABLE IF NOT EXISTS Raw_Material (
    Material_ID INT DEFAULT nextval('material_id_seq') PRIMARY KEY,
    Material_Name VARCHAR(100) NOT NULL,
    Supplier_ID INT,
    Stock_Quantity INT NOT NULL DEFAULT 0,
    Unit_Price DECIMAL(10, 2) NOT NULL,
    Reorder_Level INT NOT NULL DEFAULT 10,
    FOREIGN KEY (Supplier_ID) REFERENCES Supplier(Supplier_ID) ON DELETE SET NULL
);

-- Create Product table
CREATE TABLE IF NOT EXISTS Product (
    Product_ID INT DEFAULT nextval('product_id_seq') PRIMARY KEY,
    Product_Name VARCHAR(100) NOT NULL,
    Category_ID INT,
    Manufacturing_Cost DECIMAL(10, 2) NOT NULL,
    Selling_Price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (Category_ID) REFERENCES Category(Category_ID) ON DELETE SET NULL
);

-- Insert sample data into Supplier table
INSERT INTO Supplier (Supplier_Name, Contact_Info, Address) VALUES
('ABC Supplies', 'contact@abcsupplies.com', '123 Main St, New York, NY'),
('XYZ Materials', 'info@xyzmaterials.com', '456 Oak Ave, Los Angeles, CA'),
('Quality Goods', 'sales@qualitygoods.com', '789 Pine Rd, Chicago, IL'),
('GlobalSource Inc.', 'support@globalsource.com', '101 Maple Dr, Houston, TX'),
('Premium Vendors', 'hello@premiumvendors.com', '202 Cedar Blvd, Miami, FL');

-- Insert sample data into Category table
INSERT INTO Category (Category_Name, Description) VALUES
('Electronics', 'Electronic components and devices'),
('Furniture', 'Home and office furniture'),
('Clothing', 'Apparel and textiles'),
('Food Products', 'Edible items and ingredients'),
('Office Supplies', 'Materials used in offices');

-- Insert sample data into Raw_Material table
INSERT INTO Raw_Material (Material_Name, Supplier_ID, Stock_Quantity, Unit_Price, Reorder_Level) VALUES
('Aluminum', 1, 500, 5.99, 100),
('Plastic', 2, 1000, 2.50, 200),
('Fabric', 3, 750, 3.75, 150),
('Wood', 4, 300, 7.25, 75),
('Glass', 5, 200, 6.50, 50),
('Steel', 1, 600, 8.75, 120),
('Cotton', 3, 850, 4.25, 170),
('Paper', 5, 1500, 1.50, 300),
('Silicon', 2, 400, 12.99, 80),
('Rubber', 4, 25, 9.99, 50);

-- Insert sample data into Product table
INSERT INTO Product (Product_Name, Category_ID, Manufacturing_Cost, Selling_Price) VALUES
('Smartphone', 1, 250.00, 699.99),
('Office Chair', 2, 85.00, 199.99),
('T-Shirt', 3, 8.50, 24.99),
('Chocolate Bar', 4, 1.25, 3.99),
('Notebook', 5, 2.50, 7.99),
('Laptop', 1, 450.00, 1299.99),
('Dining Table', 2, 175.00, 499.99),
('Jeans', 3, 15.00, 49.99),
('Coffee Beans', 4, 8.00, 19.99),
('Printer Paper', 5, 3.75, 12.99);
