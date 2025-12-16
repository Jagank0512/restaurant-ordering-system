-- Database: restaurant_qr_db

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sub_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sub_category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sub_category_id) REFERENCES sub_categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(100),
    table_number VARCHAR(10),
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('UPI', 'Cash') DEFAULT 'Cash',
    payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
    order_status ENUM('Received', 'Preparing', 'Ready', 'Served') DEFAULT 'Received',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Default Admin (Password: admin123 - technically needs to be hashed in application)
-- INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$YourHashedPasswordHere');

INSERT INTO admin (username, password)
VALUES (
  'admin',
  '$2y$10$/sWpdJWGbRJ77bA9w9uTqOmMUFJQ1USAt/vvBKIF0JzQ2Tqv2y1Ha'
);

UPDATE admin
SET username = 'admin', password = '$2y$10$/sWpdJWGbRJ77bA9w9uTqOmMUFJQ1USAt/vvBKIF0JzQ2Tqv2y1Ha'
where username = 'admin'
