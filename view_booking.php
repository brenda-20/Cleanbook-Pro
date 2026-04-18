<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'admin') {
    redirect('login.php');
}

$bookingId = isset($_GET['id']) ? $_GET['id'] : 0;
$message = '';
$messageType = '';

$conn = getDBConnection();

$query = "SELECT b.*, s.service_name, s.category, s.duration_hours,
          u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
          FROM bookings b 
          JOIN services s ON b.service_id = s.service_id 
          JOIN users u ON b.customer_id = u.user_id 
          WHERE b.booking_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    redirect('admin/bookings.php');
}

$staffQuery = "SELECT user_id, full_name, phone FROM users WHERE user_type = 'staff' AND status = 'active'";
$staffMembers = $conn->query($staffQuery);

$assignedStaff = null;
$assignQuery = "SELECT sa.*, u.full_name, u.phone 
                FROM staff_assignments sa 
                JOIN users u ON sa.staff_id = u.user_id 
                WHERE sa.booking_id = ?";
$stmt = $conn->prepare($assignQuery);
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $assignedStaff = $result->fetch_assoc();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'confirm') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        if ($stmt->execute()) {
            $message = "Booking confirmed successfully!";
            $messageType = "success";
            $booking['status'] = 'confirmed';
        }
        $stmt->close();
    }
    
    elseif ($action == 'assign_staff') {
        $staffId = isset($_POST['staff_id']) ? $_POST['staff_id'] : 0;
        if ($staffId > 0) {
            $stmt = $conn->prepare("INSERT INTO staff_assignments (booking_id, staff_id, status) VALUES (?, ?, 'assigned')");
            $stmt->bind_param("ii", $bookingId, $staffId);
            if ($stmt->execute()) {
                $stmt2 = $conn->prepare("UPDATE bookings SET status = 'assigned' WHERE booking_id = ?");
                $stmt2->bind_param("i", $bookingId);
                $stmt2->execute();
                $stmt2->close();
                
                $message = "Staff assigned successfully!";
                $messageType = "success";
                $booking['status'] = 'assigned';
                
                header("refresh:1");
            }
            $stmt->close();
        }
    }
    
    elseif ($action == 'mark_completed') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        if ($stmt->execute()) {
            if ($assignedStaff) {
                $stmt2 = $conn->prepare("UPDATE staff_assignments SET status = 'completed', completed_at = NOW() WHERE assignment_id = ?");
                $stmt2->bind_param("i", $assignedStaff['assignment_id']);
                $stmt2->execute();
                $stmt2->close();
            }
            
            $message = "Booking marked as completed!";
            $messageType = "success";
            $booking['status'] = 'completed';
        }
        $stmt->close();
    }
    
    elseif ($action == 'cancel') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        if ($stmt->execute()) {
            $message = "Booking cancelled!";
            $messageType = "warning";
            $booking['status'] = 'cancelled';
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
    <title>Booking #<?php echo $bookingId; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #1E2761, #764ba2); }
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
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a href="dashboard.php" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="bookings.php" class="nav-link text-white">
                    <i class="fas fa-calendar"></i> Bookings
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
            <h2><i class="fas fa-file-alt"></i> Booking #<?php echo $bookingId; ?></h2>
            <a href="bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="info-card">
                    <h4 class="mb-3">Booking Details</h4>
                    <div class="info-row">
                        <strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?>
                        <span class="badge bg-secondary ms-2"><?php echo ucfirst($booking['category']); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Date:</strong> <?php echo formatDate($booking['booking_date'], 'l, F j, Y'); ?>
                    </div>
                    <div class="info-row">
                        <strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                    </div>
                    <div class="info-row">
                        <strong>Duration:</strong> <?php echo $booking['duration_hours']; ?> hours
                    </div>
                    <div class="info-row">
                        <strong>Address:</strong><br>
                        <?php echo nl2br(htmlspecialchars($booking['address'])); ?>
                    </div>
                    <?php if (!empty($booking['special_instructions'])): ?>
                        <div class="info-row">
                            <strong>Special Instructions:</strong><br>
                            <?php echo nl2br(htmlspecialchars($booking['special_instructions'])); ?>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <strong>Total Amount:</strong>
                        <span class="fs-4 text-primary"><?php echo formatMoney($booking['total_price']); ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-user"></i> Customer Information</h4>
                    <div class="info-row">
                        <strong>Name:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Email:</strong> <?php echo htmlspecialchars($booking['customer_email']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($booking['customer_phone']); ?>
                    </div>
                </div>

                <?php if ($assignedStaff): ?>
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-user-tie"></i> Assigned Staff</h4>
                        <div class="info-row">
                            <strong>Name:</strong> <?php echo htmlspecialchars($assignedStaff['full_name']); ?>
                        </div>
                        <div class="info-row">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($assignedStaff['phone']); ?>
                        </div>
                        <div class="info-row">
                            <strong>Assigned:</strong> <?php echo formatDate($assignedStaff['assigned_at'], 'M d, Y g:i A'); ?>
                        </div>
                        <div class="info-row">
                            <strong>Status:</strong>
                            <span class="badge bg-<?php echo $assignedStaff['status'] == 'completed' ? 'success' : 'primary'; ?>">
                                <?php echo ucfirst($assignedStaff['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="info-card">
                    <h4 class="mb-3"><i class="fas fa-tasks"></i> Actions</h4>
                    
                    <div class="alert alert-info">
                        <strong>Current Status:</strong><br>
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
                        <span class="badge bg-<?php echo $colors[$booking['status']]; ?> fs-6">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>

                    <?php if ($booking['status'] == 'pending'): ?>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="action" value="confirm">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (in_array($booking['status'], ['confirmed', 'pending']) && !$assignedStaff): ?>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="action" value="assign_staff">
                            <label class="form-label">Assign Staff Member</label>
                            <select name="staff_id" class="form-select mb-2" required>
                                <option value="">Select staff...</option>
                                <?php while ($staff = $staffMembers->fetch_assoc()): ?>
                                    <option value="<?php echo $staff['user_id']; ?>">
                                        <?php echo htmlspecialchars($staff['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-check"></i> Assign Staff
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (in_array($booking['status'], ['assigned', 'in_progress'])): ?>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="action" value="mark_completed">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check-circle"></i> Mark as Completed
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (!in_array($booking['status'], ['completed', 'cancelled'])): ?>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to cancel this booking?')">
                                <i class="fas fa-times"></i> Cancel Booking
                            </button>
                        </form>
                    <?php endif; ?>

                    <hr>

                    <div class="alert alert-<?php echo $booking['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                        <strong>Payment:</strong>
                        <span class="badge bg-<?php echo $booking['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($booking['payment_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>