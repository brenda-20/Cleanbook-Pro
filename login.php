<?php 
require_once 'config/config.php';

if (isLoggedIn()) {
    $userType = getUserType();
    if ($userType == 'admin') {
        redirect('admin/dashboard.php');
    } elseif ($userType == 'staff') {
        redirect('staff/dashboard.php');
    } else {
        redirect('customer/dashboard.php');
    }
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash, user_type, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if ($user['status'] != 'active') {
                $errors[] = "Your account has been deactivated";
            } elseif (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                if ($user['user_type'] == 'admin') {
                    redirect('admin/dashboard.php');
                } elseif ($user['user_type'] == 'staff') {
                    redirect('staff/dashboard.php');
                } else {
                    redirect('customer/dashboard.php');
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
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
    <title>Login - <?php echo SITE_NAME; ?></title>
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
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 450px;
            margin: 0 auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3rem;
            color: #028090;
        }
        .form-control:focus {
            border-color: #028090;
            box-shadow: 0 0 0 0.2rem rgba(2, 128, 144, 0.25);
        }
        .btn-login {
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
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-sign-in-alt"></i>
                <h2 class="mt-3">Welcome Back</h2>
                <p class="text-muted">Login to <?php echo SITE_NAME; ?></p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" name="email" class="form-control" 
                           value="<?php echo $email; ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="mb-2">Don't have an account?</p>
                <a href="customer/register.php" class="btn btn-outline-success w-100">
                    <i class="fas fa-user-plus"></i> Create New Account
                </a>
            </div>

            <div class="alert alert-info mt-4">
                <strong>Demo Accounts:</strong><br>
                <small>
                    <strong>Admin:</strong> admin@cleanbook.com<br>
                    <strong>Customer:</strong> john@example.com<br>
                    <strong>Staff:</strong> jane@cleanbook.com<br>
                    <strong>Password:</strong> password
                </small>
            </div>
        </div>
    </div>
</body>
</html>