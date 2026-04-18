<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Information - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #028090, #02C39A); }
        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .payment-method {
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #028090;
            box-shadow: 0 5px 15px rgba(2, 128, 144, 0.2);
        }
        .payment-icon {
            font-size: 3rem;
            color: #028090;
            margin-bottom: 15px;
        }
        .step-number {
            background: #028090;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-broom"></i> <?php echo SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserType() == 'customer'): ?>
                        <a href="customer/dashboard.php" class="nav-link text-white">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link text-white">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-link text-white">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="text-center mb-5">
            <h1><i class="fas fa-credit-card"></i> Payment Information</h1>
            <p class="lead text-muted">How to pay for your cleaning services</p>
        </div>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Payment Policy</h5>
                    <p class="mb-0">
                        At CleanBook Pro, we believe in <strong>"Pay After Service"</strong>. 
                        You only pay once our staff has completed the cleaning to your satisfaction!
                    </p>
                </div>

                <div class="payment-card">
                    <h3 class="mb-4"><i class="fas fa-money-bill-wave"></i> Available Payment Methods</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="payment-method text-center">
                                <i class="fas fa-money-bill-wave payment-icon"></i>
                                <h4>Cash Payment</h4>
                                <p class="text-muted">Pay directly to our staff member after service completion</p>
                                <span class="badge bg-success">Most Popular</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="payment-method text-center">
                                <i class="fas fa-mobile-alt payment-icon"></i>
                                <h4>M-Pesa</h4>
                                <p class="text-muted">Send payment via M-Pesa to our staff or business number</p>
                                <span class="badge bg-primary">Instant</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="payment-card">
                    <h3 class="mb-4"><i class="fas fa-list-ol"></i> How It Works</h3>

                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">1</span>
                            <div>
                                <h5>Book Your Service Online</h5>
                                <p class="text-muted">Choose your service, date, and time. No payment required at booking!</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">2</span>
                            <div>
                                <h5>We Confirm & Assign Staff</h5>
                                <p class="text-muted">Our admin team confirms your booking and assigns a qualified staff member.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">3</span>
                            <div>
                                <h5>Staff Completes the Service</h5>
                                <p class="text-muted">Our professional staff arrives on time and completes the cleaning.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <span class="step-number">4</span>
                            <div>
                                <h5>You Pay After Service</h5>
                                <p class="text-muted">Once you're satisfied, pay via cash or M-Pesa directly to our staff.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-shield-alt"></i> 
                        <strong>100% Satisfaction Guarantee:</strong> 
                        If you're not happy with the service, we'll make it right before you pay!
                    </div>
                </div>

                <div class="payment-card">
                    <h3 class="mb-4"><i class="fas fa-phone-alt"></i> M-Pesa Payment Instructions</h3>
                    
                    <p><strong>Option 1: Pay to Staff Member</strong></p>
                    <ol>
                        <li>After service completion, staff will provide their M-Pesa number</li>
                        <li>Go to M-Pesa on your phone</li>
                        <li>Select "Send Money"</li>
                        <li>Enter staff's phone number</li>
                        <li>Enter the service amount</li>
                        <li>Confirm and send</li>
                    </ol>

                    <p class="mt-4"><strong>Option 2: Pay to Business Number</strong></p>
                    <div class="alert alert-warning">
                        <strong>Business Number:</strong> <?php echo SITE_PHONE; ?><br>
                        <strong>Business Name:</strong> CleanBook Pro<br>
                        <small>Use "Lipa na M-Pesa" → "Pay Bill" → Enter booking ID as reference</small>
                    </div>
                </div>

                <div class="payment-card">
                    <h3 class="mb-4"><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>

                    <div class="mb-3">
                        <strong>Q: Do I pay when booking?</strong>
                        <p class="text-muted">No! Booking is completely FREE. You only pay after the service is completed.</p>
                    </div>

                    <div class="mb-3">
                        <strong>Q: What if I'm not satisfied with the service?</strong>
                        <p class="text-muted">Contact our support immediately. We'll send staff back to fix any issues before payment.</p>
                    </div>

                    <div class="mb-3">
                        <strong>Q: Can I pay with a credit card?</strong>
                        <p class="text-muted">Currently, we accept Cash and M-Pesa only. Credit card payment coming soon!</p>
                    </div>

                    <div class="mb-3">
                        <strong>Q: Will I get a receipt?</strong>
                        <p class="text-muted">Yes! Your booking confirmation serves as a receipt. M-Pesa payments automatically generate receipts.</p>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="<?php echo isLoggedIn() ? 'customer/services.php' : 'customer/register.php'; ?>" 
                       class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check"></i> 
                        <?php echo isLoggedIn() ? 'Book a Service Now' : 'Get Started - Register Free'; ?>
                    </a>
                </div>

                <div class="text-center mt-3">
                    <p class="text-muted">
                        <i class="fas fa-envelope"></i> Questions? Contact us: 
                        <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a> | 
                        <i class="fas fa-phone"></i> <?php echo SITE_PHONE; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2026 <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>
</body>
</html>