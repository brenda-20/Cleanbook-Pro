<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'customer') {
    redirect('login.php');
}

$conn = getDBConnection();
$userId = getUserId();
$stats = [
    'total_bookings' => 0,
    'pending' => 0,
    'completed' => 0,
    'total_spent' => 0
];

$query = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END) as total_spent
    FROM bookings WHERE customer_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

$recentQuery = "SELECT b.*, s.service_name, s.category 
                FROM bookings b 
                JOIN services s ON b.service_id = s.service_id 
                WHERE b.customer_id = ? 
                ORDER BY b.created_at DESC 
                LIMIT 5";
$stmt = $conn->prepare($recentQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$recentBookings = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #028090, #02C39A); }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card i { font-size: 2.5rem; opacity: 0.8; }
        .stat-value { font-size: 2rem; font-weight: bold; margin: 10px 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-broom"></i> <?php echo SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user"></i> <?php echo getUserName(); ?>
                </span>
                <a href="../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo getUserName(); ?>!</p>

        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-calendar-check text-primary"></i>
                    <div class="stat-value text-primary"><?php echo $stats['total_bookings']; ?></div>
                    <div class="text-muted">Total Bookings</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clock text-warning"></i>
                    <div class="stat-value text-warning"><?php echo $stats['pending']; ?></div>
                    <div class="text-muted">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-check-circle text-success"></i>
                    <div class="stat-value text-success"><?php echo $stats['completed']; ?></div>
                    <div class="text-muted">Completed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-money-bill-wave text-info"></i>
                    <div class="stat-value text-info"><?php echo formatMoney($stats['total_spent'] ?? 0); ?></div>
                    <div class="text-muted">Total Spent</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <a href="services.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus"></i> Book New Service
                        </a>
                        <a href="my_bookings.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Recent Bookings
                    </div>
                    <div class="card-body">
                        <?php if ($recentBookings->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = $recentBookings->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $booking['booking_id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['service_name']); ?>
                                                    <br><small class="text-muted"><?php echo ucfirst($booking['category']); ?></small>
                                                </td>
                                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                                <td>
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
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatMoney($booking['total_price']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No bookings yet</p>
                                <a href="services.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Book Your First Service
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>