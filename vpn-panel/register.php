<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (strlen($username) < 3) {
        $error = "Username must be at least 3 characters!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username or email exists
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username or email already exists!";
        } else {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, email, password, balance) VALUES ('$username', '$email', '$hashed_password', 10.00)";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Registration successful! You can now login. Welcome bonus: $10.00";
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AlrelShop VPN Panel V.4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px 20px 0 0;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-container">
                    <div class="register-header">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h3 class="mb-1">Create Account</h3>
                        <p class="mb-0">Join AlrelShop VPN Panel V.4</p>
                    </div>
                    <div class="p-4">
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
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Username
                                </label>
                                <input type="text" class="form-control" name="username" required placeholder="Enter username" value="<?= isset($_POST['username']) ? $_POST['username'] : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" name="email" required placeholder="Enter email" value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control" name="password" required placeholder="Enter password" minlength="6">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Confirm Password
                                </label>
                                <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm password" minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary btn-register w-100">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        <div class="text-center">
                            <p class="mb-2">Already have an account?</p>
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt"></i> Login Here
                            </a>
                        </div>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-gift"></i> Get $10.00 welcome bonus!<br>
                                <i class="fas fa-phone"></i> Support: 082285851668<br>
                                <i class="fas fa-globe"></i> AlrelShop &copy; 2024
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>