<?php 
require_once '../config/config.php';

if (!isLoggedIn() || getUserType() != 'customer') {
    redirect('login.php');
}

$conn = getDBConnection();
$selectedCategory = $_GET['category'] ?? 'all';

if ($selectedCategory == 'all') {
    $query = "SELECT * FROM services WHERE status = 'active' ORDER BY category, service_name";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT * FROM services WHERE status = 'active' AND category = ? ORDER BY service_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selectedCategory);
}

$stmt->execute();
$services = $stmt->get_result();
$stmt->close();

$categoryCounts = [];
$countQuery = "SELECT category, COUNT(*) as count FROM services WHERE status = 'active' GROUP BY category";
$countResult = $conn->query($countQuery);
while ($row = $countResult->fetch_assoc()) {
    $categoryCounts[$row['category']] = $row['count'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #028090, #02C39A); }
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .service-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #028090;
        }
        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .filter-btn { margin: 5px; }
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
                <a href="my_bookings.php" class="nav-link text-white">
                    <i class="fas fa-list"></i> My Bookings
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
        <div class="row mb-4">
            <div class="col-md-12">
                <h2><i class="fas fa-th-large"></i> Our Services</h2>
                <p class="text-muted">Browse and book professional cleaning services</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter"></i> Filter by Category</h5>
                        <div>
                            <a href="?category=all" 
                               class="btn <?php echo $selectedCategory == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                                <i class="fas fa-th"></i> All Services
                            </a>
                            <a href="?category=automotive" 
                               class="btn <?php echo $selectedCategory == 'automotive' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                                <i class="fas fa-car"></i> Automotive 
                                <span class="badge bg-secondary"><?php echo $categoryCounts['automotive'] ?? 0; ?></span>
                            </a>
                            <a href="?category=residential" 
                               class="btn <?php echo $selectedCategory == 'residential' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                                <i class="fas fa-home"></i> Residential 
                                <span class="badge bg-secondary"><?php echo $categoryCounts['residential'] ?? 0; ?></span>
                            </a>
                            <a href="?category=furniture" 
                               class="btn <?php echo $selectedCategory == 'furniture' ? 'btn-primary' : 'btn-outline-primary'; ?> filter-btn">
                                <i class="fas fa-couch"></i> Furniture 
                                <span class="badge bg-secondary"><?php echo $categoryCounts['furniture'] ?? 0; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if ($services->num_rows > 0): ?>
                <?php 
                $icons = [
                    'automotive' => 'fa-car',
                    'residential' => 'fa-home',
                    'furniture' => 'fa-couch'
                ];
                $colors = [
                    'automotive' => 'text-primary',
                    'residential' => 'text-success',
                    'furniture' => 'text-warning'
                ];
                ?>
                <?php while ($service = $services->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="service-card position-relative">
                            <span class="category-badge badge bg-<?php 
                                echo $service['category'] == 'automotive' ? 'primary' : 
                                     ($service['category'] == 'residential' ? 'success' : 'warning'); 
                            ?>">
                                <?php echo ucfirst($service['category']); ?>
                            </span>

                            <div class="text-center">
                                <i class="fas <?php echo $icons[$service['category']]; ?> service-icon <?php echo $colors[$service['category']]; ?>"></i>
                            </div>

                            <h5 class="text-center mb-3"><?php echo htmlspecialchars($service['service_name']); ?></h5>

                            <p class="text-muted small"><?php echo htmlspecialchars($service['description']); ?></p>

                            <p class="mb-2">
                                <i class="fas fa-clock text-muted"></i>
                                <strong>Duration:</strong> <?php echo $service['duration_hours']; ?> hours
                            </p>

                            <div class="text-center mb-3">
                                <div class="price-tag"><?php echo formatMoney($service['base_price']); ?></div>
                                <small class="text-muted">Base Price</small>
                            </div>

                            <a href="book_service.php?service_id=<?php echo $service['service_id']; ?>" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-calendar-check"></i> Book This Service
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-md-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No services available in this category
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>