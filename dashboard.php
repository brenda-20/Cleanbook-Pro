<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'staff') {
    redirect('login.php');
}

$conn = getDBConnection();
$staffId = getUserId();

$stats = [
    'total_jobs' => 0,
    'assigned' => 0,
    'in_progress' => 0,
    'completed' => 0
];

$query = "SELECT 
    COUNT(*) as total_jobs,
    SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM staff_assignments 
    WHERE staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

$today = date('Y-m-d');
$todayQuery = "SELECT sa.*, b.booking_date, b.booking_time, b.address, 
                s.service_name, u.full_name as customer_name, u.phone as customer_phone
                FROM staff_assignments sa
                JOIN bookings b ON sa.booking_id = b.booking_id
                JOIN services s ON b.service_id = s.service_id
                JOIN users u ON b.customer_id = u.user_id
                WHERE sa.staff_id = ? AND b.booking_date = ?
                ORDER BY b.booking_time ASC";
$stmt = $conn->prepare($todayQuery);
$stmt->bind_param("is", $staffId, $today);
$stmt->execute();
$todayJobs = $stmt->get_result();
$stmt->close();

$upcomingQuery = "SELECT sa.*, b.booking_date, b.booking_time, b.address,
                  s.service_name, u.full_name as customer_name
                  FROM staff_assignments sa
                  JOIN bookings b ON sa.booking_id = b.booking_id
                  JOIN services s ON b.service_id = s.service_id
                  JOIN users u ON b.customer_id = u.user_id
                  WHERE sa.staff_id = ? AND b.booking_date > ?
                  AND sa.status != 'completed'
                  ORDER BY b.booking_date ASC, b.booking_time ASC
                  LIMIT 5";
$stmt = $conn->prepare($upcomingQuery);
$stmt->bind_param("is", $staffId, $today);
$stmt->execute();
$upcomingJobs = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #2C5F2D, #97BC62); }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card i { font-size: 2.5rem; opacity: 0.8; }
        .stat-value { font-size: 2rem; font-weight: bold; margin: 10px 0; }
        .job-card {
            background: white;
            border-left: 4px solid #2C5F2D;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-tie"></i> Staff Portal
            </a>
            <div class="navbar-nav ms-auto">
                <a href="my_jobs.php" class="nav-link text-white">
                    <i class="fas fa-tasks"></i> My Jobs
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

    <div class="container mt-4">
        <h2><i class="fas fa-tachometer-alt"></i> Staff Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo getUserName(); ?>!</p>

        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-briefcase text-primary"></i>
                    <div class="stat-value text-primary"><?php echo $stats['total_jobs']; ?></div>
                    <div class="text-muted">Total Jobs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clock text-warning"></i>
                    <div class="stat-value text-warning"><?php echo $stats['assigned']; ?></div>
                    <div class="text-muted">Assigned</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-spinner text-info"></i>
                    <div class="stat-value text-info"><?php echo $stats['in_progress']; ?></div>
                    <div class="text-muted">In Progress</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-check-circle text-success"></i>
                    <div class="stat-value text-success"><?php echo $stats['completed']; ?></div>
                    <div class="text-muted">Completed</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-calendar-day"></i> Today's Jobs (<?php echo date('l, F j, Y'); ?>)
                    </div>
                    <div class="card-body">
                        <?php if ($todayJobs->num_rows > 0): ?>
                            <?php while ($job = $todayJobs->fetch_assoc()): ?>
                                <div class="job-card">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5><?php echo htmlspecialchars($job['service_name']); ?></h5>
                                            <p class="mb-1">
                                                <i class="fas fa-user text-muted"></i>
                                                <strong>Customer:</strong> <?php echo htmlspecialchars($job['customer_name']); ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-phone text-muted"></i>
                                                <strong>Phone:</strong> <?php echo htmlspecialchars($job['customer_phone']); ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-clock text-muted"></i>
                                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($job['booking_time'])); ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-map-marker-alt text-muted"></i>
                                                <strong>Address:</strong> <?php echo htmlspecialchars($job['address']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-<?php 
                                                echo $job['status'] == 'completed' ? 'success' : 
                                                    ($job['status'] == 'in_progress' ? 'info' : 'warning'); 
                                            ?> mb-3">
                                                <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                            </span><br>
                                            <a href="view_job.php?id=<?php echo $job['assignment_id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No jobs scheduled for today</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt"></i> Upcoming Jobs
                    </div>
                    <div class="card-body">
                        <?php if ($upcomingJobs->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Service</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($job = $upcomingJobs->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo formatDate($job['booking_date']); ?></td>
                                                <td><?php echo date('g:i A', strtotime($job['booking_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($job['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($job['customer_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-warning">
                                                        <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_job.php?id=<?php echo $job['assignment_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center">
                                <a href="my_jobs.php" class="btn btn-primary">
                                    <i class="fas fa-list"></i> View All Jobs
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No upcoming jobs</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>