<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'staff') {
    redirect('login.php');
}

$conn = getDBConnection();
$staffId = getUserId();

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($statusFilter == 'all') {
    $query = "SELECT sa.*, b.booking_date, b.booking_time, b.address, b.total_price,
              s.service_name, s.duration_hours, u.full_name as customer_name, u.phone as customer_phone
              FROM staff_assignments sa
              JOIN bookings b ON sa.booking_id = b.booking_id
              JOIN services s ON b.service_id = s.service_id
              JOIN users u ON b.customer_id = u.user_id
              WHERE sa.staff_id = ?
              ORDER BY b.booking_date DESC, b.booking_time DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staffId);
} else {
    $query = "SELECT sa.*, b.booking_date, b.booking_time, b.address, b.total_price,
              s.service_name, s.duration_hours, u.full_name as customer_name, u.phone as customer_phone
              FROM staff_assignments sa
              JOIN bookings b ON sa.booking_id = b.booking_id
              JOIN services s ON b.service_id = s.service_id
              JOIN users u ON b.customer_id = u.user_id
              WHERE sa.staff_id = ? AND sa.status = ?
              ORDER BY b.booking_date DESC, b.booking_time DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $staffId, $statusFilter);
}

$stmt->execute();
$jobs = $stmt->get_result();
$stmt->close();

$counts = [];
$countQuery = "SELECT status, COUNT(*) as count FROM staff_assignments WHERE staff_id = ? GROUP BY status";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$countResult = $stmt->get_result();
while ($row = $countResult->fetch_assoc()) {
    $counts[$row['status']] = $row['count'];
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Jobs - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #2C5F2D, #97BC62); }
        .filter-btn { margin: 5px; }
        .job-row {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-user-tie"></i> Staff Portal
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
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
        <h2><i class="fas fa-tasks"></i> My Jobs</h2>
        <p class="text-muted">View and manage your assigned jobs</p>

        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="fas fa-filter"></i> Filter by Status</h5>
                <a href="?status=all" class="btn <?php echo $statusFilter == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                    All Jobs
                </a>
                <a href="?status=assigned" class="btn <?php echo $statusFilter == 'assigned' ? 'btn-warning' : 'btn-outline-warning'; ?> filter-btn">
                    Assigned <span class="badge bg-secondary"><?php echo $counts['assigned'] ?? 0; ?></span>
                </a>
                <a href="?status=in_progress" class="btn <?php echo $statusFilter == 'in_progress' ? 'btn-info' : 'btn-outline-info'; ?> filter-btn">
                    In Progress <span class="badge bg-secondary"><?php echo $counts['in_progress'] ?? 0; ?></span>
                </a>
                <a href="?status=completed" class="btn <?php echo $statusFilter == 'completed' ? 'btn-success' : 'btn-outline-success'; ?> filter-btn">
                    Completed <span class="badge bg-secondary"><?php echo $counts['completed'] ?? 0; ?></span>
                </a>
            </div>
        </div>

        <?php if ($jobs->num_rows > 0): ?>
            <?php while ($job = $jobs->fetch_assoc()): ?>
                <div class="job-row">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>
                                <?php echo htmlspecialchars($job['service_name']); ?>
                                <span class="badge bg-<?php 
                                    echo $job['status'] == 'completed' ? 'success' : 
                                        ($job['status'] == 'in_progress' ? 'info' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                </span>
                            </h5>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="fas fa-user text-muted"></i>
                                        <strong>Customer:</strong> <?php echo htmlspecialchars($job['customer_name']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-phone text-muted"></i>
                                        <strong>Phone:</strong> <?php echo htmlspecialchars($job['customer_phone']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-calendar text-muted"></i>
                                        <strong>Date:</strong> <?php echo formatDate($job['booking_date'], 'l, M d, Y'); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="fas fa-clock text-muted"></i>
                                        <strong>Time:</strong> <?php echo date('g:i A', strtotime($job['booking_time'])); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-hourglass-half text-muted"></i>
                                        <strong>Duration:</strong> <?php echo $job['duration_hours']; ?> hours
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                        <strong>Address:</strong> <?php echo htmlspecialchars($job['address']); ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($job['notes']): ?>
                                <div class="alert alert-info mt-2 mb-0">
                                    <strong><i class="fas fa-sticky-note"></i> Notes:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($job['notes'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 text-end">
                            <div class="mb-3">
                                <small class="text-muted">Assigned:</small><br>
                                <strong><?php echo formatDate($job['assigned_at'], 'M d, Y'); ?></strong>
                            </div>

                            <?php if ($job['completed_at']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Completed:</small><br>
                                    <strong><?php echo formatDate($job['completed_at'], 'M d, Y'); ?></strong>
                                </div>
                            <?php endif; ?>

                            <a href="view_job.php?id=<?php echo $job['assignment_id']; ?>" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-eye"></i> View Details
                            </a>

                            <?php if ($job['status'] == 'assigned'): ?>
                                <a href="view_job.php?id=<?php echo $job['assignment_id']; ?>&action=start" 
                                   class="btn btn-success w-100 mt-2">
                                    <i class="fas fa-play"></i> Start Job
                                </a>
                            <?php endif; ?>

                            <?php if ($job['status'] == 'in_progress'): ?>
                                <a href="view_job.php?id=<?php echo $job['assignment_id']; ?>&action=complete" 
                                   class="btn btn-success w-100 mt-2">
                                    <i class="fas fa-check"></i> Complete Job
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No jobs found</h4>
                <p class="text-muted">You don't have any jobs with this status</p>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>