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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Professional Cleaning Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #028090 0%, #02C39A 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .feature-card {
            padding: 30px;
            text-align: center;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-card i {
            font-size: 3rem;
            color: #028090;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1 class="display-3"><i class="fas fa-broom"></i> <?php echo SITE_NAME; ?></h1>
            <p class="lead">Professional Cleaning Services at Your Doorstep</p>
            <div class="mt-5">
                <a href="login.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="customer/register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <h2 class="text-center mb-5">Our Services</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card feature-card">
                    <i class="fas fa-car"></i>
                    <h4>Automotive Cleaning</h4>
                    <p>Professional car washing and detailing services</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card">
                    <i class="fas fa-home"></i>
                    <h4>Residential Cleaning</h4>
                    <p>Complete house and office cleaning solutions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card">
                    <i class="fas fa-couch"></i>
                    <h4>Furniture Cleaning</h4>
                    <p>Expert sofa, carpet, and mattress cleaning</p>
                </div>
            </div>
        </div>
    </div>
<div class="container my-5">
    <div class="text-center">
        <h3>Transparent Pricing</h3>
        <p class="text-muted">No hidden fees. Pay only after you're satisfied!</p>
        <a href="payment_info.php" class="btn btn-outline-primary">
            <i class="fas fa-info-circle"></i> View Payment Information
        </a>
    </div>
</div>
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2026 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p class="mb-0">Developed by Riziki Brenda - Machakos University</p>
        </div>
    </footer>
</body>
</html>