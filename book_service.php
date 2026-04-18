<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'customer') {
    redirect('login.php');
}

$serviceId = $_GET['service_id'] ?? 0;

if (empty($serviceId)) {
    redirect('customer/services.php');
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM services WHERE service_id = ? AND status = 'active'");
$stmt->bind_param("i", $serviceId);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$service) {
    redirect('customer/services.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_date = sanitize($_POST['booking_date']);
    $booking_time = sanitize($_POST['booking_time']);
    $address = sanitize($_POST['address']);
    $special_instructions = sanitize($_POST['special_instructions'] ?? '');
    $total_price = $service['base_price'];
    
    if (empty($booking_date)) {
        $errors[] = "Booking date is required";
    } else {
        $today = date('Y-m-d');
        if ($booking_date < $today) {
            $errors[] = "Booking date must be in the future";
        }
    }
    
    if (empty($booking_time)) {
        $errors[] = "Booking time is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($errors)) {
        $userId = getUserId();
        
        $stmt = $conn->prepare("INSERT INTO bookings (customer_id, service_id, booking_date, booking_time, address, special_instructions, total_price, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')");
        $stmt->bind_param("iissssd", $userId, $serviceId, $booking_date, $booking_time, $address, $special_instructions, $total_price);
        
        if ($stmt->execute()) {
            $bookingId = $stmt->insert_id;
            $success = "Booking created successfully! Booking ID: #" . $bookingId . "<br><small>Payment will be collected after service completion. <a href='../payment_info.php' class='alert-link'>View payment options</a></small>";
            header("refresh:2;url=my_bookings.php");
        } else {
            $errors[] = "Failed to create booking. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #028090, #02C39A); }
        .booking-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .service-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .price-highlight {
            font-size: 2rem;
            font-weight: bold;
            color: #028090;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-broom"></i> <?php echo SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="services.php" class="nav-link text-white">
                    <i class="fas fa-th-large"></i> Services
                </a>
                <span class="nav-link text-white">
                    <i class="fas fa-user"></i> <?php echo getUserName(); ?>
                </span>
                <a href="../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="booking-card">
                    <h2 class="mb-4">
                        <i class="fas fa-calendar-check"></i> Book Service
                    </h2>

                    <div class="service-summary">
                        <h4><?php echo htmlspecialchars($service['service_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-tag text-muted"></i>
                                    <strong>Category:</strong> <?php echo ucfirst($service['category']); ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-clock text-muted"></i>
                                    <strong>Duration:</strong> <?php echo $service['duration_hours']; ?> hours
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="price-highlight"><?php echo formatMoney($service['base_price']); ?></div>
                                <small class="text-muted">Total Amount</small>
                            </div>
                        </div>
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
                            <p class="mb-0 mt-2">Redirecting to your bookings...</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Booking Date *
                                </label>
                                <input type="date" name="booking_date" class="form-control" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                       required>
                                <small class="text-muted">Select a future date</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock"></i> Preferred Time *
                                </label>
                                <select name="booking_time" class="form-control" required>
                                    <option value="">Select time...</option>
                                    <option value="08:00:00">8:00 AM</option>
                                    <option value="09:00:00">9:00 AM</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="11:00:00">11:00 AM</option>
                                    <option value="12:00:00">12:00 PM</option>
                                    <option value="13:00:00">1:00 PM</option>
                                    <option value="14:00:00">2:00 PM</option>
                                    <option value="15:00:00">3:00 PM</option>
                                    <option value="16:00:00">4:00 PM</option>
                                    <option value="17:00:00">5:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Service Address *
                            </label>
                            <textarea name="address" class="form-control" rows="3" 
                                      placeholder="Enter your full address including building/house number, street, area..." 
                                      required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-comment"></i> Special Instructions (Optional)
                            </label>
                            <textarea name="special_instructions" class="form-control" rows="3" 
                                      placeholder="Any specific requirements or instructions for our staff..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Booking Summary</h6>
                            <div class="d-flex justify-content-between">
                                <span>Service:</span>
                                <strong><?php echo htmlspecialchars($service['service_name']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Duration:</span>
                                <strong><?php echo $service['duration_hours']; ?> hours</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Total Amount:</strong></span>
                                <strong class="text-primary"><?php echo formatMoney($service['base_price']); ?></strong>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-shield-alt"></i> Payment will be collected after service completion
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                            <a href="services.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Services
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>