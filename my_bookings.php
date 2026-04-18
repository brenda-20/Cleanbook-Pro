<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'customer') {
    redirect('login.php');
}

$conn = getDBConnection();
$userId = getUserId();

$query = "SELECT b.*, s.service_name, s.category, s.duration_hours 
          FROM bookings b 
          JOIN services s ON b.service_id = s.service_id 
          WHERE b.customer_id = ? 
          ORDER BY b.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #028090, #02C39A); }
        .booking-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
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

    <div class="container mt-4 mb-4">
        <h2><i class="fas fa-list"></i> My Bookings</h2>
        <p class="text-muted">View and manage your service bookings</p>

        <?php if ($bookings->num_rows > 0): ?>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <h5 class="mb-0">Booking #<?php echo $booking['booking_id']; ?></h5>
                            <small class="text-muted">
                                Created: <?php echo formatDate($booking['created_at'], 'M d, Y g:i A'); ?>
                            </small>
                        </div>
                        <div>
                            <?php
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'assigned' => 'primary',
                                'in_progress' => 'secondary',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $color = $statusColors[$booking['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?> fs-6">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <h6><i class="fas fa-wrench"></i> <?php echo htmlspecialchars($booking['service_name']); ?></h6>
                            <p class="mb-2">
                                <i class="fas fa-tag text-muted"></i>
                                <span class="badge bg-secondary"><?php echo ucfirst($booking['category']); ?></span>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-calendar text-muted"></i>
                                <strong>Date:</strong> <?php echo formatDate($booking['booking_date'], 'l, F j, Y'); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-clock text-muted"></i>
                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt text-muted"></i>
                                <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($booking['address'])); ?>
                            </p>
                            <?php if (!empty($booking['special_instructions'])): ?>
                                <p class="mb-2">
                                    <i class="fas fa-comment text-muted"></i>
                                    <strong>Instructions:</strong> <?php echo nl2br(htmlspecialchars($booking['special_instructions'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo formatMoney($booking['total_price']); ?></h4>
                                <p class="mb-2">
                                    <span class="badge bg-<?php echo $booking['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </p>
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-hourglass-half"></i> Awaiting confirmation
                                        <?php if ($booking['status'] == 'completed' && $booking['payment_status'] == 'unpaid'): ?>
    <small class="text-warning d-block mt-2">
        <i class="fas fa-exclamation-circle"></i> Payment pending
        <a href="../payment_info.php" class="text-warning">Payment options</a>
    </small>
<?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No bookings yet</h4>
                <p class="text-muted">Start by booking your first cleaning service</p>
                <a href="services.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Browse Services
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>