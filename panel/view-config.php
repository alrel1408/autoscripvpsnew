<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$account_id = (int)$_GET['id'];

// Get account details
$account_query = "
    SELECT va.*, s.name as server_name, s.location, s.ip_address, s.provider 
    FROM vpn_accounts va 
    JOIN servers s ON va.server_id = s.id 
    WHERE va.id = $account_id AND va.user_id = $user_id
";
$account_result = mysqli_query($conn, $account_query);

if (mysqli_num_rows($account_result) == 0) {
    header('Location: my-accounts.php');
    exit;
}

$account = mysqli_fetch_assoc($account_result);

// Get configurations
$config_query = "SELECT * FROM configurations WHERE account_id = $account_id";
$configs = mysqli_query($conn, $config_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config Details - AlrelShop VPN Panel V.4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <style>
        .config-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .config-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        .config-data {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
            margin: 15px 0;
            position: relative;
        }
        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
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
            <a href="create-account.php" class="nav-link">
                <i class="fas fa-plus-circle"></i> Create Account
            </a>
            <a href="my-accounts.php" class="nav-link active">
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
                <h1><i class="fas fa-eye"></i> Account Configuration</h1>
                <p>Configuration details for <?= $account['username'] ?></p>
            </div>
        </div>

        <div class="container-fluid">
            <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Account created successfully! Your configuration is ready.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <!-- Account Information -->
                    <div class="config-card">
                        <h4><i class="fas fa-info-circle"></i> Account Information</h4>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>ID Account:</strong> <?= str_pad($account['id'], 8, '0', STR_PAD_LEFT) ?></p>
                                <p><strong>Username:</strong> <?= $account['username'] ?></p>
                                <p><strong>Password:</strong> <?= $account['password'] ?></p>
                                <p><strong>Service Type:</strong> <?= strtoupper($account['service_type']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Server:</strong> <?= $account['server_name'] ?></p>
                                <p><strong>Location:</strong> <?= $account['location'] ?></p>
                                <p><strong>Expired Date:</strong> <?= date('d M Y', strtotime($account['expired_date'])) ?></p>
                                <p><strong>Status:</strong> 
                                    <?php if ($account['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Files -->
                    <?php if (mysqli_num_rows($configs) > 0): ?>
                        <?php while ($config = mysqli_fetch_assoc($configs)): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5><i class="fas fa-cog"></i> <?= strtoupper($config['config_type']) ?> Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="config-data">
                                    <button class="copy-btn" onclick="copyToClipboard('config-<?= $config['id'] ?>')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    <div id="config-<?= $config['id'] ?>">
                                        <?php if ($config['config_type'] == 'vmess'): ?>
                                            vmess://<?= base64_encode($config['config_data']) ?>
                                        <?php else: ?>
                                            <?= $config['config_data'] ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- QR Code -->
                                <div class="qr-code">
                                    <div id="qr-<?= $config['id'] ?>"></div>
                                    <p class="mt-2"><small>Scan QR Code with your VPN app</small></p>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Connection Guide -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-book"></i> Connection Guide</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($account['service_type'] == 'ssh'): ?>
                            <h6>SSH/OpenVPN Connection</h6>
                            <p><strong>Server:</strong> <?= $account['ip_address'] ?></p>
                            <p><strong>Port SSH:</strong> 22, 2222</p>
                            <p><strong>Port SSL:</strong> 443, 8443</p>
                            <p><strong>Port Dropbear:</strong> 143, 109</p>
                            <p><strong>OpenVPN SSL:</strong> 1194 (TCP/UDP)</p>
                            
                            <?php elseif ($account['service_type'] == 'vmess'): ?>
                            <h6>VMess Connection</h6>
                            <p>1. Copy the VMess configuration above</p>
                            <p>2. Import into your V2Ray client</p>
                            <p>3. Connect and enjoy!</p>
                            
                            <?php elseif ($account['service_type'] == 'vless'): ?>
                            <h6>VLess Connection</h6>
                            <p>1. Copy the VLess URL above</p>
                            <p>2. Import into your V2Ray/Xray client</p>
                            <p>3. Connect and enjoy!</p>
                            
                            <?php elseif ($account['service_type'] == 'trojan'): ?>
                            <h6>Trojan Connection</h6>
                            <p>1. Copy the Trojan URL above</p>
                            <p>2. Import into your Trojan client</p>
                            <p>3. Connect and enjoy!</p>
                            
                            <?php elseif ($account['service_type'] == 'shadowsocks'): ?>
                            <h6>Shadowsocks Connection</h6>
                            <p>1. Copy the SS URL above</p>
                            <p>2. Import into your Shadowsocks client</p>
                            <p>3. Connect and enjoy!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Download Apps -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-download"></i> Recommended Apps</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($account['service_type'] == 'ssh'): ?>
                                <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fab fa-android"></i> ConnectBot (Android)
                                </a>
                                <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fab fa-apple"></i> Termius (iOS)
                                </a>
                                <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-desktop"></i> PuTTY (Windows)
                                </a>
                            <?php else: ?>
                                <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fab fa-android"></i> V2rayNG (Android)
                                </a>
                                <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fab fa-apple"></i> Shadowrocket (iOS)
                                </a>
                                <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-desktop"></i> V2rayN (Windows)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-headset"></i> Need Help?</h5>
                        </div>
                        <div class="card-body text-center">
                            <p>Contact our support team</p>
                            <a href="https://wa.me/082285851668" class="btn btn-success" target="_blank">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>

                    <!-- Account Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-tools"></i> Account Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="my-accounts.php" class="btn btn-secondary w-100 mb-2">
                                <i class="fas fa-arrow-left"></i> Back to Accounts
                            </a>
                            <button class="btn btn-info w-100 mb-2" onclick="refreshAccount()">
                                <i class="fas fa-sync"></i> Refresh Config
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        // Generate QR codes
        document.addEventListener('DOMContentLoaded', function() {
            <?php mysqli_data_seek($configs, 0); ?>
            <?php while ($config = mysqli_fetch_assoc($configs)): ?>
            const config<?= $config['id'] ?> = document.getElementById('config-<?= $config['id'] ?>').textContent.trim();
            QRCode.toCanvas(document.getElementById('qr-<?= $config['id'] ?>'), config<?= $config['id'] ?>, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff'
                }
            });
            <?php endwhile; ?>
        });

        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent.trim();
            
            navigator.clipboard.writeText(text).then(function() {
                // Change button text temporarily
                const btn = element.parentElement.querySelector('.copy-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                }, 2000);
            });
        }

        function refreshAccount() {
            location.reload();
        }
    </script>
</body>
</html>