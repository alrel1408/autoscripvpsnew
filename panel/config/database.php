<?php
// Database Configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = 'AlrelShop2024!';
$db_name = 'vpn_panel';

// Connect to database
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Create tables if not exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'reseller', 'user') DEFAULT 'user',
        balance DECIMAL(10,2) DEFAULT 0.00,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS servers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        location VARCHAR(100) NOT NULL,
        provider VARCHAR(50) NOT NULL,
        ssh_port INT DEFAULT 22,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        max_accounts INT DEFAULT 50,
        current_accounts INT DEFAULT 0,
        status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
        monthly_price DECIMAL(8,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS vpn_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        server_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(100) NOT NULL,
        service_type ENUM('ssh', 'vmess', 'vless', 'trojan', 'shadowsocks') NOT NULL,
        expired_date DATE NOT NULL,
        max_ip INT DEFAULT 2,
        status ENUM('active', 'expired', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (server_id) REFERENCES servers(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('deposit', 'purchase', 'commission') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS configurations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT NOT NULL,
        config_type VARCHAR(50) NOT NULL,
        config_data TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES vpn_accounts(id)
    )"
];

foreach ($tables as $table) {
    mysqli_query($conn, $table);
}

// Create admin user if not exists
$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) == 0) {
    $admin_password = password_hash('AlrelShop2024', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, email, password, role, balance) VALUES ('admin', 'admin@alrelshop.com', '$admin_password', 'admin', 1000000.00)");
}

// Add sample servers if empty
$server_check = mysqli_query($conn, "SELECT id FROM servers LIMIT 1");
if (mysqli_num_rows($server_check) == 0) {
    $sample_servers = [
        "INSERT INTO servers (name, ip_address, location, provider, username, password, monthly_price) VALUES 
        ('Digital Ocean SG 1', '178.128.29.122', 'Singapore', 'digitalocean', 'root', 'Vallstore@2024', 25.00),
        ('Digital Ocean SG 2', '159.89.200.123', 'Singapore', 'digitalocean', 'root', 'ServerPass123', 25.00),
        ('Digital Ocean US 1', '192.241.200.45', 'New York', 'digitalocean', 'root', 'ServerPass456', 30.00)"
    ];
    
    foreach ($sample_servers as $server) {
        mysqli_query($conn, $server);
    }
}
?>