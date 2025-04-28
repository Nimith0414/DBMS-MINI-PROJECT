CREATE DATABASE INVENTORY3;
USE INVENTORY3;

-- Creating the Supplier table
CREATE TABLE Supplier (
    Supplier_ID INT PRIMARY KEY AUTO_INCREMENT,
    Supplier_Name VARCHAR(255) NOT NULL,
    Contact_Info VARCHAR(255),
    Address TEXT
);

-- Creating the Raw Material table
CREATE TABLE Raw_Material (
    Material_ID INT PRIMARY KEY AUTO_INCREMENT,
    Material_Name VARCHAR(255) NOT NULL,
    Supplier_ID INT,
    Stock_Quantity INT DEFAULT 0,
    Unit_Price DECIMAL(10,2),
    Reorder_Level INT DEFAULT 10,
    FOREIGN KEY (Supplier_ID) REFERENCES Supplier(Supplier_ID) ON DELETE SET NULL
);

-- Creating the Category table
CREATE TABLE Category (
    Category_ID INT PRIMARY KEY AUTO_INCREMENT,
    Category_Name VARCHAR(255) NOT NULL,
    Description TEXT
);

-- Creating the Product table
CREATE TABLE Product (
    Product_ID INT PRIMARY KEY AUTO_INCREMENT,
    Product_Name VARCHAR(255) NOT NULL,
    Category_ID INT,
    Manufacturing_Cost DECIMAL(10,2),
    Selling_Price DECIMAL(10,2),
    Stock_Quantity INT DEFAULT 0,
    FOREIGN KEY (Category_ID) REFERENCES Category(Category_ID) ON DELETE SET NULL
);

-- Creating the Machine table
CREATE TABLE Machine (
    Machine_ID INT PRIMARY KEY AUTO_INCREMENT,
    Machine_Name VARCHAR(255) NOT NULL,
    Machine_Function TEXT,  -- Renamed from "Function" to "Machine_Function"
    Status ENUM('Operational', 'Under Maintenance', 'Out of Service'),
    Last_Maintenance_Date DATE
);


CREATE TABLE Warehouse (
    Warehouse_ID INT PRIMARY KEY AUTO_INCREMENT,
    Warehouse_Location VARCHAR(255) NOT NULL,
    Capacity INT DEFAULT 0
);

CREATE TABLE Customer (
    Customer_ID INT PRIMARY KEY AUTO_INCREMENT,
    Customer_Name VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE,
    Phone_Number VARCHAR(20),
    Address TEXT
);

CREATE TABLE Sales_Order (
    Order_ID INT PRIMARY KEY AUTO_INCREMENT,
    Customer_ID INT,
    Order_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
    Total_Amount DECIMAL(10,2),
    Status ENUM('Pending', 'Shipped', 'Delivered', 'Cancelled'),
    FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID) ON DELETE CASCADE
);

CREATE TABLE Order_Details (
    OrderDetail_ID INT PRIMARY KEY AUTO_INCREMENT,
    Order_ID INT,
    Product_ID INT,
    Quantity INT,
    Subtotal DECIMAL(10,2),
    FOREIGN KEY (Order_ID) REFERENCES Sales_Order(Order_ID) ON DELETE CASCADE,
    FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID) ON DELETE CASCADE
);