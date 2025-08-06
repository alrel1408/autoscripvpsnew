<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get available servers
$servers_query = "SELECT * FROM servers WHERE status = 'active' ORDER BY location, name";
$servers = mysqli_query($conn, $servers_query);

// Pricing configuration
$pricing = [
    'ssh' => 3.00,
    'vmess' => 5.00,
    'vless' => 5.00,
    'trojan' => 6.00,
    'shadowsocks' => 4.00
];

$error = '';
$success = '';

if ($_POST) {
    $server_id = (int)$_POST['server_id'];
    $service_type = mysqli_real_escape_string($conn, $_POST['service_type']);
    $duration = (int)$_POST['duration'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Calculate price
    $price = $pricing[$service_type] * $duration;
    
    // Check balance
    if ($user['balance'] < $price) {
        $error = "Insufficient balance! Required: $" . number_format($price, 2) . ", Available: $" . number_format($user['balance'], 2);
    } else {
        // Check if username already exists
        $check_user = mysqli_query($conn, "SELECT id FROM vpn_accounts WHERE username = '$username'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = "Username already exists! Please choose a different username.";
        } else {
            // Get server details
            $server_query = "SELECT * FROM servers WHERE id = $server_id";
            $server_result = mysqli_query($conn, $server_query);
            $server = mysqli_fetch_assoc($server_result);
            
            if ($server) {
                // Calculate expiry date
                $expired_date = date('Y-m-d', strtotime("+$duration days"));
                
                // Create account in database
                $create_query = "INSERT INTO vpn_accounts (user_id, server_id, username, password, service_type, expired_date, status) 
                                VALUES ($user_id, $server_id, '$username', '$password', '$service_type', '$expired_date', 'active')";
                
                if (mysqli_query($conn, $create_query)) {
                    $account_id = mysqli_insert_id($conn);
                    
                    // Deduct balance
                    $new_balance = $user['balance'] - $price;
                    mysqli_query($conn, "UPDATE users SET balance = $new_balance WHERE id = $user_id");
                    
                    // Add transaction record
                    mysqli_query($conn, "INSERT INTO transactions (user_id, type, amount, description, status) 
                                        VALUES ($user_id, 'purchase', -$price, 'VPN Account: $username ($service_type)', 'success')");
                    
                    // Create account on server via SSH
                    $create_success = createVPNAccount($server, $username, $password, $service_type, $duration);
                    
                    if ($create_success) {
                        // Generate configuration
                        generateConfig($account_id, $server, $username, $password, $service_type);
                        
                        $success = "Account created successfully! Username: $username";
                        $_SESSION['balance'] = $new_balance;
                        
                        // Redirect to view config
                        header("Location: view-config.php?id=$account_id&created=1");
                        exit;
                    } else {
                        $error = "Failed to create account on server. Please contact support.";
                    }
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                }
            } else {
                $error = "Invalid server selected!";
            }
        }
    }
}

function createVPNAccount($server, $username, $password, $service_type, $duration) {
    // SSH connection to create account
    $connection = ssh2_connect($server['ip_address'], $server['ssh_port']);
    
    if ($connection && ssh2_auth_password($connection, $server['username'], $server['password'])) {
        $command = '';
        
        switch ($service_type) {
            case 'ssh':
                $command = "addssh $username $password $duration";
                break;
            case 'vmess':
                $command = "addws $username $password $duration";
                break;
            case 'vless':
                $command = "addvless $username $password $duration";
                break;
            case 'trojan':
                $command = "addtr $username $password $duration";
                break;
            case 'shadowsocks':
                $command = "addss $username $password $duration";
                break;
        }
        
        if ($command) {
            $stream = ssh2_exec($connection, $command);
            stream_set_blocking($stream, true);
            $output = stream_get_contents($stream);
            fclose($stream);
            return true;
        }
    }
    return false;
}

function generateConfig($account_id, $server, $username, $password, $service_type) {
    global $conn;
    
    $domain = "matcha.alrelshop.my.id"; // You can make this dynamic
    $config_data = '';
    
    switch ($service_type) {
        case 'vmess':
            $config_data = json_encode([
                'v' => '2',
                'ps' => "AlrelShop-{$server['location']}-VMESS",
                'add' => $domain,
                'port' => '443',
                'id' => generateUUID(),
                'aid' => '0',
                'scy' => 'auto',
                'net' => 'ws',
                'type' => 'none',
                'host' => $domain,
                'path' => "/vmess-$username",
                'tls' => 'tls',
                'sni' => $domain,
                'alpn' => ''
            ]);
            break;
            
        case 'vless':
            $config_data = "vless://" . generateUUID() . "@$domain:443?encryption=none&security=tls&sni=$domain&type=ws&host=$domain&path=%2Fvless-$username#AlrelShop-{$server['location']}-VLESS";
            break;
            
        case 'trojan':
            $config_data = "trojan://$password@$domain:443?security=tls&sni=$domain&type=ws&host=$domain&path=%2Ftrojan-$username#AlrelShop-{$server['location']}-TROJAN";
            break;
            
        case 'shadowsocks':
            $method = 'aes-128-gcm';
            $encoded = base64_encode("$method:$password");
            $config_data = "ss://$encoded@$domain:443#AlrelShop-{$server['location']}-SS";
            break;
            
        case 'ssh':
            $config_data = json_encode([
                'server' => $server['ip_address'],
                'port' => 22,
                'username' => $username,
                'password' => $password,
                'ssl_port' => 443,
                'ssl_sni' => $domain
            ]);
            break;
    }
    
    if ($config_data) {
        mysqli_query($conn, "INSERT INTO configurations (account_id, config_type, config_data) 
                            VALUES ($account_id, '$service_type', '" . mysqli_real_escape_string($conn, $config_data) . "')");
    }
}

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - AlrelShop VPN Panel V.4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i>
            <span>AlrelShop Panel</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="create-account.php" class="nav-link active">
                <i class="fas fa-plus-circle"></i> Create Account
            </a>
            <a href="my-accounts.php" class="nav-link">
                <i class="fas fa-list"></i> My Accounts
            </a>
            <a href="servers.php" class="nav-link">
                <i class="fas fa-server"></i> Servers
            </a>
            <a href="balance.php" class="nav-link">
                <i class="fas fa-wallet"></i> Balance
            </a>
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h1><i class="fas fa-plus-circle"></i> Create VPN Account</h1>
                <p>Create a new VPN account on your preferred server</p>
            </div>
            <div class="header-right">
                <div class="balance-display">
                    <i class="fas fa-wallet"></i> 
                    Balance: <strong>$<?= number_format($user['balance'], 2) ?></strong>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-cog"></i> Account Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="createForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-server"></i> Select Server
                                            </label>
                                            <select name="server_id" class="form-select" required onchange="updatePrice()">
                                                <option value="">Choose Server...</option>
                                                <?php while ($server = mysqli_fetch_assoc($servers)): ?>
                                                <option value="<?= $server['id'] ?>" data-location="<?= $server['location'] ?>" data-ip="<?= $server['ip_address'] ?>">
                                                    <?= $server['name'] ?> - <?= $server['location'] ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-shield-alt"></i> Service Type
                                            </label>
                                            <select name="service_type" class="form-select" required onchange="updatePrice()">
                                                <option value="">Choose Service...</option>
                                                <option value="ssh">SSH/OpenVPN - $3.00/month</option>
                                                <option value="vmess">VMess - $5.00/month</option>
                                                <option value="vless">VLess - $5.00/month</option>
                                                <option value="trojan">Trojan - $6.00/month</option>
                                                <option value="shadowsocks">Shadowsocks - $4.00/month</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-user"></i> Username
                                            </label>
                                            <input type="text" name="username" class="form-control" required 
                                                   placeholder="Enter username" pattern="[a-zA-Z0-9_-]+" 
                                                   title="Only letters, numbers, underscore and dash allowed">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-lock"></i> Password
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="password" class="form-control" required 
                                                       placeholder="Enter password" id="passwordField">
                                                <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                                    <i class="fas fa-random"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-calendar"></i> Duration (Days)
                                            </label>
                                            <select name="duration" class="form-select" required onchange="updatePrice()">
                                                <option value="">Select Duration...</option>
                                                <option value="30">30 Days (1 Month)</option>
                                                <option value="60">60 Days (2 Months)</option>
                                                <option value="90">90 Days (3 Months)</option>
                                                <option value="180">180 Days (6 Months)</option>
                                                <option value="365">365 Days (1 Year)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-dollar-sign"></i> Total Price
                                            </label>
                                            <div class="form-control bg-light" id="totalPrice">
                                                Select service and duration
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="createBtn">
                                        <i class="fas fa-plus-circle"></i> Create Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Pricing Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="pricing-list">
                                <?php foreach ($pricing as $service => $price): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= strtoupper($service) ?>:</span>
                                    <strong>$<?= number_format($price, 2) ?>/month</strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i> 99.9% Uptime Guaranteed<br>
                                    <i class="fas fa-headset"></i> 24/7 Support Available<br>
                                    <i class="fas fa-rocket"></i> High Speed Servers
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5><i class="fas fa-question-circle"></i> Need Help?</h5>
                        </div>
                        <div class="card-body text-center">
                            <p>Contact our support team for assistance</p>
                            <a href="https://wa.me/082285851668" class="btn btn-success" target="_blank">
                                <i class="fab fa-whatsapp"></i> WhatsApp Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pricing = <?= json_encode($pricing) ?>;

        function updatePrice() {
            const serviceType = document.querySelector('[name="service_type"]').value;
            const duration = document.querySelector('[name="duration"]').value;
            const priceDisplay = document.getElementById('totalPrice');

            if (serviceType && duration) {
                const basePrice = pricing[serviceType];
                const totalPrice = (basePrice * parseInt(duration) / 30).toFixed(2);
                priceDisplay.innerHTML = `<strong>$${totalPrice}</strong>`;
                priceDisplay.className = 'form-control bg-success text-white';
            } else {
                priceDisplay.innerHTML = 'Select service and duration';
                priceDisplay.className = 'form-control bg-light';
            }
        }

        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('passwordField').value = password;
        }

        // Form validation
        document.getElementById('createForm').addEventListener('submit', function(e) {
            const button = document.getElementById('createBtn');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            button.disabled = true;
        });
    </script>
</body>
</html>