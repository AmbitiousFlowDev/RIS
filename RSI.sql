-- ========================================
-- Drop tables if they exist
-- ========================================
DROP TABLE IF EXISTS Discount;
DROP TABLE IF EXISTS Bonus;
DROP TABLE IF EXISTS Audit;
DROP TABLE IF EXISTS OrderLine;
DROP TABLE IF EXISTS `Order`;
DROP TABLE IF EXISTS Product;
DROP TABLE IF EXISTS Client;
DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS Employee;
DROP TABLE IF EXISTS Role;

-- ========================================
-- Table: Role
-- ========================================
CREATE TABLE Role (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_code VARCHAR(50) NOT NULL UNIQUE,
    deleted_at DATETIME NULL
);

INSERT INTO Role (role_code) VALUES
('USER_HR'),
('USER_COMMERCIAL'),
('USER_DIRECTION'),
('USER_PURCHASING');

-- ========================================
-- Table: Employee
-- ========================================
CREATE TABLE Employee (
    employee_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_employee_role
        FOREIGN KEY (role_id)
        REFERENCES Role(role_id)
);

INSERT INTO Employee (first_name, last_name, role_id) VALUES
('John', 'Doe', 3),
('Alice', 'Smith', 2),
('Bob', 'Johnson', 2),
('Emma', 'Brown', 1);

-- ========================================
-- Table: User
-- ========================================
CREATE TABLE User (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_category VARCHAR(50) NOT NULL,
    employee_id INT NOT NULL UNIQUE,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_user_employee
        FOREIGN KEY (employee_id)
        REFERENCES Employee(employee_id)
        ON DELETE CASCADE
);

INSERT INTO User (username, password_hash, user_category, employee_id) VALUES
('admin', MD5('admin123'), 'ADMIN', 1),
('alice', MD5('sales123'), 'EMPLOYEE', 2),
('bob', MD5('sales456'), 'EMPLOYEE', 3),
('emma', MD5('account123'), 'EMPLOYEE', 4);

-- ========================================
-- Table: Client
-- ========================================
CREATE TABLE Client (
    client_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    deleted_at DATETIME NULL
);

INSERT INTO Client (first_name, last_name, address, city) VALUES
('Michael', 'Scott', '1725 Slough Ave', 'Scranton'),
('Pam', 'Beesly', 'Main Street 12', 'Scranton'),
('Jim', 'Halpert', 'Market Road 5', 'Stamford');

-- ========================================
-- Table: Product
-- ========================================
CREATE TABLE Product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    name VARCHAR(150) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0),
    stock_quantity INT NOT NULL CHECK (stock_quantity >= 0),
    deleted_at DATETIME NULL
);

INSERT INTO Product (category, name, unit_price, stock_quantity) VALUES
('Electronics', 'Laptop', 1200.00, 20),
('Electronics', 'Mouse', 25.00, 150),
('Office', 'Desk Chair', 180.00, 50),
('Office', 'Notebook', 3.50, 500);

-- ========================================
-- Table: Order
-- ========================================
CREATE TABLE `Order` (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    order_date DATE NOT NULL,
    client_id INT NOT NULL,
    employee_id INT NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_order_client
        FOREIGN KEY (client_id)
        REFERENCES Client(client_id),
    CONSTRAINT fk_order_employee
        FOREIGN KEY (employee_id)
        REFERENCES Employee(employee_id)
);

INSERT INTO `Order` (order_date, client_id, employee_id) VALUES
('2025-01-10', 1, 2),
('2025-01-15', 2, 3),
('2025-02-01', 3, 2);

-- ========================================
-- Table: OrderLine
-- ========================================
CREATE TABLE OrderLine (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0),
    deleted_at DATETIME NULL,
    PRIMARY KEY (order_id, product_id),
    CONSTRAINT fk_orderline_order
        FOREIGN KEY (order_id)
        REFERENCES `Order`(order_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_orderline_product
        FOREIGN KEY (product_id)
        REFERENCES Product(product_id)
);

INSERT INTO OrderLine (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 1200.00),
(1, 2, 2, 25.00),
(2, 3, 1, 180.00),
(3, 4, 10, 3.50);

-- ========================================
-- Table: Audit
-- ========================================
CREATE TABLE Audit (
    audit_id INT PRIMARY KEY AUTO_INCREMENT,
    action_type VARCHAR(50) NOT NULL,
    action_date DATETIME NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES User(user_id)
);

INSERT INTO Audit (action_type, action_date, description, user_id) VALUES
('LOGIN', NOW(), 'Admin logged in', 1),
('CREATE_ORDER', NOW(), 'Order created for client Michael Scott', 2),
('UPDATE_PRODUCT', NOW(), 'Updated stock for Notebook', 1);

-- ========================================
-- Table: Bonus
-- ========================================
CREATE TABLE Bonus (
    bonus_id INT PRIMARY KEY AUTO_INCREMENT,
    year INT NOT NULL,
    turnover DECIMAL(12,2) NOT NULL CHECK (turnover >= 0),
    bonus_amount DECIMAL(12,2) NOT NULL CHECK (bonus_amount >= 0),
    employee_id INT NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_bonus_employee
        FOREIGN KEY (employee_id)
        REFERENCES Employee(employee_id),
    CONSTRAINT uq_bonus_employee_year
        UNIQUE (employee_id, year)
);

INSERT INTO Bonus (year, turnover, bonus_amount, employee_id) VALUES
(2024, 150000.00, 7500.00, 1),
(2024, 90000.00, 4500.00, 2),
(2024, 85000.00, 4250.00, 3);

-- ========================================
-- Table: Discount
-- ========================================
CREATE TABLE Discount (
    discount_id INT PRIMARY KEY AUTO_INCREMENT,
    year INT NOT NULL,
    total_purchases DECIMAL(12,2) NOT NULL CHECK (total_purchases >= 0),
    discount_amount DECIMAL(12,2) NOT NULL CHECK (discount_amount >= 0),
    client_id INT NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_discount_client
        FOREIGN KEY (client_id)
        REFERENCES Client(client_id),
    CONSTRAINT uq_discount_client_year
        UNIQUE (client_id, year)
);

INSERT INTO Discount (year, total_purchases, discount_amount, client_id) VALUES
(2024, 5000.00, 250.00, 1),
(2024, 3200.00, 160.00, 2),
(2024, 4100.00, 205.00, 3);