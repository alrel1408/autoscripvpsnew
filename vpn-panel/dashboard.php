<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get statistics
$total_accounts = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM vpn_accounts WHERE user_id = $user_id"));
$active_accounts = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM vpn_accounts WHERE user_id = $user_id AND status = 'active'"));
$expired_accounts = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM vpn_accounts WHERE user_id = $user_id AND status = 'expired'"));

// Get recent accounts
$recent_accounts = mysqli_query($conn, "
    SELECT va.*, s.name as server_name, s.location 
    FROM vpn_accounts va 
    JOIN servers s ON va.server_id = s.id 
    WHERE va.user_id = $user_id 
    ORDER BY va.created_at DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AlrelShop VPN Panel V.4</title>
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
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="create-account.php" class="nav-link">
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
            <?php if ($user['role'] == 'admin'): ?>
            <a href="admin.php" class="nav-link">
                <i class="fas fa-cog"></i> Admin Panel
            </a>
            <?php endif; ?>
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Welcome back, <?= $user['username'] ?>!</p>
            </div>
            <div class="header-right">
                <div class="balance-display">
                    <i class="fas fa-wallet"></i> 
                    Balance: <strong>$<?= number_format($user['balance'], 2) ?></strong>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-primary">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $total_accounts ?></h3>
                            <p>Total Accounts</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $active_accounts ?></h3>
                            <p>Active Accounts</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $expired_accounts ?></h3>
                            <p>Expired Accounts</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-info">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$<?= number_format($user['balance'], 0) ?></h3>
                            <p>Current Balance</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="create-account.php" class="btn btn-primary btn-lg w-100 mb-2">
                                        <i class="fas fa-plus-circle"></i><br>
                                        Create New Account
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="my-accounts.php" class="btn btn-success btn-lg w-100 mb-2">
                                        <i class="fas fa-list"></i><br>
                                        View My Accounts
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="balance.php" class="btn btn-warning btn-lg w-100 mb-2">
                                        <i class="fas fa-credit-card"></i><br>
                                        Add Balance
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="servers.php" class="btn btn-info btn-lg w-100 mb-2">
                                        <i class="fas fa-server"></i><br>
                                        Browse Servers
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Accounts -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Recent Accounts</h5>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($recent_accounts) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Server</th>
                                            <th>Service</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($account = mysqli_fetch_assoc($recent_accounts)): ?>
                                        <tr>
                                            <td>
                                                <strong><?= $account['username'] ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-server"></i> <?= $account['server_name'] ?><br>
                                                <small class="text-muted"><?= $account['location'] ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= strtoupper($account['service_type']) ?></span>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($account['expired_date'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($account['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php elseif ($account['status'] == 'expired'): ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Suspended</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view-config.php?id=<?= $account['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View Config
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No accounts found</h5>
                                <p class="text-muted">Create your first VPN account to get started!</p>
                                <a href="create-account.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Account
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>