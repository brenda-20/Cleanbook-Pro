<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'admin') {
    redirect('login.php');
}

$conn = getDBConnection();

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($statusFilter == 'all') {
    $query = "SELECT b.*, s.service_name, u.full_name as customer_name, u.phone as customer_phone 
              FROM bookings b 
              JOIN services s ON b.service_id = s.service_id 
              JOIN users u ON b.customer_id = u.user_id 
              ORDER BY b.created_at DESC";
    $bookings = $conn->query($query);
} else {
    $stmt = $conn->prepare("SELECT b.*, s.service_name, u.full_name as customer_name, u.phone as customer_phone 
              FROM bookings b 
              JOIN services s ON b.service_id = s.service_id 
              JOIN users u ON b.customer_id = u.user_id 
              WHERE b.status = ?
              ORDER BY b.created_at DESC");
    $stmt->bind_param("s", $statusFilter);
    $stmt->execute();
    $bookings = $stmt->get_result();
    $stmt->close();
}

$counts = [];
$countQuery = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
$countResult = $conn->query($countQuery);
while ($row = $countResult->fetch_assoc()) {
    $counts[$row['status']] = $row['count'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #1E2761, #764ba2); }
        .filter-btn { margin: 5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
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

    <div class="container-fluid mt-4 mb-4">
        <h2><i class="fas fa-calendar-alt"></i> Manage Bookings</h2>
        <p class="text-muted">View and manage all service bookings</p>

        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="fas fa-filter"></i> Filter by Status</h5>
                <a href="?status=all" class="btn <?php echo $statusFilter == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                    All Bookings
                </a>
                <a href="?status=pending" class="btn <?php echo $statusFilter == 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?> filter-btn">
                    Pending <span class="badge bg-secondary"><?php echo $counts['pending'] ?? 0; ?></span>
                </a>
                <a href="?status=confirmed" class="btn <?php echo $statusFilter == 'confirmed' ? 'btn-info' : 'btn-outline-info'; ?> filter-btn">
                    Confirmed <span class="badge bg-secondary"><?php echo $counts['confirmed'] ?? 0; ?></span>
                </a>
                <a href="?status=assigned" class="btn <?php echo $statusFilter == 'assigned' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                    Assigned <span class="badge bg-secondary"><?php echo $counts['assigned'] ?? 0; ?></span>
                </a>
                <a href="?status=completed" class="btn <?php echo $statusFilter == 'completed' ? 'btn-success' : 'btn-outline-success'; ?> filter-btn">
                    Completed <span class="badge bg-secondary"><?php echo $counts['completed'] ?? 0; ?></span>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $booking['booking_id']; ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['customer_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['customer_phone']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                    <td>
                                        <?php echo formatDate($booking['booking_date']); ?><br>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></small>
                                    </td>
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
                                    <td>
                                        <span class="badge bg-<?php echo $booking['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo formatMoney($booking['total_price']); ?></strong></td>
                                    <td>
                                        <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
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
</body>
</html>