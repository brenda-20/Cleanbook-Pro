<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'staff') {
    redirect('login.php');
}

$assignmentId = isset($_GET['id']) ? $_GET['id'] : 0;
$staffId = getUserId();
$message = '';
$messageType = '';

$conn = getDBConnection();

$query = "SELECT sa.*, b.booking_date, b.booking_time, b.address, b.special_instructions, b.total_price, b.payment_status,
          s.service_name, s.category, s.description, s.duration_hours,
          u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
          FROM staff_assignments sa
          JOIN bookings b ON sa.booking_id = b.booking_id
          JOIN services s ON b.service_id = s.service_id
          JOIN users u ON b.customer_id = u.user_id
          WHERE sa.assignment_id = ? AND sa.staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $assignmentId, $staffId);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$job) {
    redirect('staff/my_jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['action'])) {
    $action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
    
    if ($action == 'start' && $job['status'] == 'assigned') {
        $stmt = $conn->prepare("UPDATE staff_assignments SET status = 'in_progress' WHERE assignment_id = ?");
        $stmt->bind_param("i", $assignmentId);
        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("UPDATE bookings SET status = 'in_progress' WHERE booking_id = ?");
            $stmt2->bind_param("i", $job['booking_id']);
            $stmt2->execute();
            $stmt2->close();
            
            $message = "Job started successfully!";
            $messageType = "success";
            $job['status'] = 'in_progress';
        }
        $stmt->close();
    }
    
    elseif ($action == 'complete' && $job['status'] == 'in_progress') {
        $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
        
        $stmt = $conn->prepare("UPDATE staff_assignments SET status = 'completed', completed_at = NOW(), notes = ? WHERE assignment_id = ?");
        $stmt->bind_param("si", $notes, $assignmentId);
        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE booking_id = ?");
            $stmt2->bind_param("i", $job['booking_id']);
            $stmt2->execute();
            $stmt2->close();
            
            $message = "Job completed successfully!";
            $messageType = "success";
            $job['status'] = 'completed';
            header("refresh:2;url=my_jobs.php");
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
    <title>Job Details - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #2C5F2D, #97BC62); }
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child { border-bottom: none; }
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
                <a href="my_jobs.php" class="nav-link text-white">
                    <i class="fas fa-tasks"></i> My Jobs
                </a>
                <a href="../logout.php" class="nav-link text-white">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-briefcase"></i> Job Details</h2>
            <a href="my_jobs.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to My Jobs
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-wrench"></i> Service Information</h4>
                    <div class="info-row">
                        <strong>Service:</strong> <?php echo htmlspecialchars($job['service_name']); ?>
                        <span class="badge bg-secondary ms-2"><?php echo ucfirst($job['category']); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Description:</strong><br>
                        <?php echo htmlspecialchars($job['description']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Date:</strong> <?php echo formatDate($job['booking_date'], 'l, F j, Y'); ?>
                    </div>
                    <div class="info-row">
                        <strong>Time:</strong> <?php echo date('g:i A', strtotime($job['booking_time'])); ?>
                    </div>
                    <div class="info-row">
                        <strong>Duration:</strong> <?php echo $job['duration_hours']; ?> hours
                    </div>
                    <div class="info-row">
                        <strong>Service Address:</strong><br>
                        <?php echo nl2br(htmlspecialchars($job['address'])); ?>
                    </div>
                    <?php if (!empty($job['special_instructions'])): ?>
                        <div class="info-row">
                            <strong>Special Instructions:</strong><br>
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo nl2br(htmlspecialchars($job['special_instructions'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-user"></i> Customer Information</h4>
                    <div class="info-row">
                        <strong>Name:</strong> <?php echo htmlspecialchars($job['customer_name']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Phone:</strong> 
                        <a href="tel:<?php echo $job['customer_phone']; ?>">
                            <?php echo htmlspecialchars($job['customer_phone']); ?>
                        </a>
                    </div>
                    <div class="info-row">
                        <strong>Email:</strong> 
                        <a href="mailto:<?php echo $job['customer_email']; ?>">
                            <?php echo htmlspecialchars($job['customer_email']); ?>
                        </a>
                    </div>
                </div>

                <?php if ($job['notes']): ?>
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-sticky-note"></i> Completion Notes</h4>
                        <div class="alert alert-info mb-0">
                            <?php echo nl2br(htmlspecialchars($job['notes'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-info-circle"></i> Job Status</h4>
                    
                    <div class="alert alert-<?php 
                        echo $job['status'] == 'completed' ? 'success' : 
                            ($job['status'] == 'in_progress' ? 'info' : 'warning'); 
                    ?>">
                        <strong>Current Status:</strong><br>
                        <span class="badge bg-<?php 
                            echo $job['status'] == 'completed' ? 'success' : 
                                ($job['status'] == 'in_progress' ? 'info' : 'warning'); 
                        ?> fs-6">
                            <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Assigned:</small><br>
                        <strong><?php echo formatDate($job['assigned_at'], 'M d, Y g:i A'); ?></strong>
                    </div>

                    <?php if ($job['completed_at']): ?>
                        <div class="mb-3">
                            <small class="text-muted">Completed:</small><br>
                            <strong><?php echo formatDate($job['completed_at'], 'M d, Y g:i A'); ?></strong>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <?php if ($job['status'] == 'assigned'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="start">
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-play"></i> Start This Job
                            </button>
                        </form>
                        <p class="text-muted small">Click when you arrive at the location and begin work</p>
                    <?php endif; ?>

                    <?php if ($job['status'] == 'in_progress'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="complete">
                            <div class="mb-3">
                                <label class="form-label">Completion Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="4" 
                                          placeholder="Any notes or observations about the completed work..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Are you sure you want to mark this job as completed?')">
                                <i class="fas fa-check-circle"></i> Mark as Completed
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($job['status'] == 'completed'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Job Completed!</strong><br>
                            Great work!
                        </div>
                    <?php endif; ?>

                    <hr>

                    <div class="alert alert-info">
                        <strong>Service Amount:</strong><br>
                        <span class="fs-4"><?php echo formatMoney($job['total_price']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>