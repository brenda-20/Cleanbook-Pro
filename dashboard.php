<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'admin') {
    redirect('login.php');
}

$conn = getDBConnection();

$stats = [
    'total_bookings' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'total_revenue' => 0,
    'total_customers' => 0,
    'total_staff' => 0,
    'active_services' => 0
];

$query = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'confirmed' OR status = 'assigned' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END) as total_revenue
    FROM bookings";
$result = $conn->query($query);
$bookingStats = $result->fetch_assoc();
$stats = array_merge($stats, $bookingStats);

$stats['total_customers'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'")->fetch_assoc()['count'];
$stats['total_staff'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'staff'")->fetch_assoc()['count'];
$stats['active_services'] = $conn->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'")->fetch_assoc()['count'];

$recentQuery = "SELECT b.*, s.service_name, u.full_name as customer_name 
                FROM bookings b 
                JOIN services s ON b.service_id = s.service_id 
                JOIN users u ON b.customer_id = u.user_id 
                ORDER BY b.created_at DESC 
                LIMIT 10";
$recentBookings = $conn->query($recentQuery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #1E2761, #764ba2); }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card i { font-size: 2.5rem; opacity: 0.8; }
        .stat-value { font-size: 2rem; font-weight: bold; margin: 10px 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a href="bookings.php" class="nav-link text-white">
                    <i class="fas fa-calendar"></i> Bookings
                </a>
                <span class="nav-link text-white">
                    <i class="fas fa-user-shield"></i> <?php echo getUserName(); ?>
                </span>
                <a href="../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
        <p class="text-muted">System Overview & Statistics</p>

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
                    <div class="text-muted">Pending Approval</div>
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
                    <div class="stat-value text-info"><?php echo formatMoney($stats['total_revenue']); ?></div>
                    <div class="text-muted">Total Revenue</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-users text-primary"></i>
                    <div class="stat-value text-primary"><?php echo $stats['total_customers']; ?></div>
                    <div class="text-muted">Customers</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-user-tie text-secondary"></i>
                    <div class="stat-value text-secondary"><?php echo $stats['total_staff']; ?></div>
                    <div class="text-muted">Staff Members</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="fas fa-wrench text-success"></i>
                    <div class="stat-value text-success"><?php echo $stats['active_services']; ?></div>
                    <div class="text-muted">Active Services</div>
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
                        <a href="bookings.php" class="btn btn-warning me-2">
                            <i class="fas fa-clock"></i> View Pending Bookings
                            <?php if ($stats['pending'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['pending']; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Recent Bookings
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $recentBookings->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                            <td><?php echo formatDate($booking['booking_date']); ?></td>
                                            <td>
                                                <?php
                                                $colors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'assigned' => 'primary',
                                                    'in_progress' => 'secondary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $colors[$booking['status']]; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatMoney($booking['total_price']); ?></td>
                                            <td>
                                                <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>