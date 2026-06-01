CREATE DATABASE siagri_marketplace;

USE siagri_marketplace;

CREATE TABLE users (
    id_user INT(100) PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255),
    role ENUM('user','supplier','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id_product INT (100) PRIMARY KEY,
    supplier_id INT,
    name VARCHAR(150),
    description TEXT,
    price DECIMAL(10,2),
    stock INT,
    category VARCHAR(100),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart (
    id_cart INT(100) PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT
);

CREATE TABLE orders (
    id_order INT (100) PRIMARY KEY,
    user_id INT,
    total_price DECIMAL(10,2),
    status ENUM('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id_item INT (100) PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2)
);

CREATE TABLE discussions (
    id_discussion INT (100) PRIMARY KEY,
    user_id INT,
    title VARCHAR(200),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
    id_comment INT(100) PRIMARY KEY,
    discussion_id INT,
    user_id INT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE consultations (
    id_consultation INT(100) PRIMARY KEY,
    user_id INT,
    expert_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);