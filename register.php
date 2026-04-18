<?php 
require_once '../config/config.php';

if (isLoggedIn()) {
    redirect('customer/dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash, user_type) VALUES (?, ?, ?, ?, 'customer')");
        $stmt->bind_param("ssss", $full_name, $email, $phone, $password_hash);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            $full_name = $email = $phone = '';
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #028090 0%, #02C39A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 50px 0;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header i {
            font-size: 3rem;
            color: #028090;
        }
        .form-control:focus {
            border-color: #028090;
            box-shadow: 0 0 0 0.2rem rgba(2, 128, 144, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #028090, #02C39A);
            border: none;
            padding: 12px;
            font-weight: bold;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2 class="mt-3">Create Account</h2>
                <p class="text-muted">Join CleanBook Pro Today</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <a href="../login.php" class="alert-link">Login now</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
    <div class="mb-3">
        <label class="form-label">
            <i class="fas fa-user"></i> Full Name
        </label>
        <input type="text" name="full_name" class="form-control" 
               value="<?php echo isset($full_name) ? $full_name : ''; ?>" 
               placeholder="Enter your full name" required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label">
            <i class="fas fa-envelope"></i> Email Address
        </label>
        <input type="email" name="email" class="form-control" 
               value="<?php echo isset($email) ? $email : ''; ?>" 
               placeholder="your.email@example.com" required>
    </div>

    <div class="mb-3">
        <label class="form-label">
            <i class="fas fa-phone"></i> Phone Number
        </label>
        <input type="tel" name="phone" class="form-control" 
               placeholder="+254712345678"
               value="<?php echo isset($phone) ? $phone : ''; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">
            <i class="fas fa-lock"></i> Password
        </label>
        <input type="password" name="password" class="form-control" 
               placeholder="Minimum 6 characters"
               minlength="6" required>
        <small class="text-muted">At least 6 characters</small>
    </div>

    <div class="mb-3">
        <label class="form-label">
            <i class="fas fa-lock"></i> Confirm Password
        </label>
        <input type="password" name="confirm_password" class="form-control" 
               placeholder="Re-enter your password" required>
    </div>

    <button type="submit" class="btn btn-primary btn-register">
        <i class="fas fa-user-plus"></i> Create Account
    </button>
</form>