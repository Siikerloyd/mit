<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/config.php';
// Get summary for dashboard
$sql = "SELECT COUNT(*) as total_products, SUM(stock) as total_stock, 
SUM(CASE WHEN stock < 10 AND stock > 0 THEN 1 ELSE 0 END) as low_stock, 
SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock FROM products";
$stats = $conn->query($sql)->fetch_assoc();

// Get recent products
$recentProducts = [];
$res = $conn->query("SELECT id, product_name, sku, image_path, created_at FROM products ORDER BY created_at DESC LIMIT 5");
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $recentProducts[] = $r;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Inventory Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/product_management.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <img src="assets/logo.svg" alt="ForgeStock" class="logo">
            <span class="app-label">Inventory</span>
        </div>
        <div class="header-right">
            <a href="logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </header>
    <div class="main-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="nav">
                <span class="nav-label">Workspace</span>
                <a href="product_management.php" class="nav-item">
                    <i class="fa-solid fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="stock_management.php" class="nav-item">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Stock</span>
                </a>
                <a href="index.php" class="nav-item active">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </nav>
        </aside>
        <main class="main-content">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title-group">
                        <h1 class="page-title"><i class="fa-solid fa-chart-pie"></i> Dashboard</h1>
                        <span class="page-subtitle">Inventory overview and recent activity</span>
                    </div>
                    <a href="add_product.php" class="btn-primary" style="text-decoration:none;"><i class="fa-solid fa-plus"></i> Add Product</a>
                </div>

                <!-- KPI Cards -->
                <?php
                $tp = (int)$stats['total_products'] ?: 1;
                $low = (int)$stats['low_stock'];
                $out = (int)$stats['out_of_stock'];
                $good = $tp - $low - $out;
                $goodPct = round(100 * $good / $tp);
                $lowPct = round(100 * $low / $tp);
                $outPct = 100 - $goodPct - $lowPct;
                ?>
                <div class="kpi-grid">
                    <div class="kpi-card kpi-total">
                        <div class="kpi-label">Total Products</div>
                        <div class="kpi-value"><?php echo (int)$stats['total_products']; ?></div>
                        <i class="fa-solid fa-box kpi-icon"></i>
                    </div>
                    <div class="kpi-card kpi-stock">
                        <div class="kpi-label">Units in Stock</div>
                        <div class="kpi-value"><?php echo number_format((int)$stats['total_stock']); ?></div>
                        <i class="fa-solid fa-cubes kpi-icon"></i>
                    </div>
                    <div class="kpi-card kpi-low">
                        <div class="kpi-label">Low Stock</div>
                        <div class="kpi-value"><?php echo (int)$stats['low_stock']; ?></div>
                        <i class="fa-solid fa-triangle-exclamation kpi-icon"></i>
                    </div>
                    <div class="kpi-card kpi-out">
                        <div class="kpi-label">Out of Stock</div>
                        <div class="kpi-value"><?php echo (int)$stats['out_of_stock']; ?></div>
                        <i class="fa-solid fa-circle-xmark kpi-icon"></i>
                    </div>
                </div>

                <!-- Stock Distribution -->
                <div class="distribution-block">
                    <div class="distribution-label">Stock Health Distribution</div>
                    <div class="distribution-track">
                        <div class="seg-in"  style="width:<?php echo $goodPct; ?>%" title="In Stock (<?php echo $goodPct; ?>%)"></div>
                        <div class="seg-low" style="width:<?php echo $lowPct; ?>%" title="Low Stock (<?php echo $lowPct; ?>%)"></div>
                        <div class="seg-out" style="width:<?php echo $outPct; ?>%" title="Out of Stock (<?php echo $outPct; ?>%)"></div>
                    </div>
                    <div class="distribution-legend">
                        <span class="legend-item"><span class="legend-dot in"></span> In Stock &mdash; <?php echo $goodPct; ?>%</span>
                        <span class="legend-item"><span class="legend-dot low"></span> Low Stock &mdash; <?php echo $lowPct; ?>%</span>
                        <span class="legend-item"><span class="legend-dot out"></span> Out of Stock &mdash; <?php echo $outPct; ?>%</span>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="recent-block">
                    <div class="recent-header">
                        <span class="recent-title"><i class="fa-solid fa-clock-rotate-left" style="margin-right:6px;"></i>Recently Added</span>
                        <a href="product_management.php" class="btn-secondary" style="font-size:0.78rem;padding:6px 12px;text-decoration:none;">View all</a>
                    </div>
                    <div class="table-container">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th style="width:52px;"></th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentProducts)): ?>
                                    <tr>
                                        <td colspan="4" class="table-empty">
                                            <i class="fa-solid fa-box-open"></i>
                                            No products yet — add your first product to get started.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentProducts as $r): ?>
                                        <tr>
                                            <td>
                                            <?php if (!empty($r['image_path']) && file_exists($r['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($r['image_path']); ?>" alt="<?php echo htmlspecialchars($r['product_name']); ?>" class="product-image-thumb">
                                            <?php else: ?>
                                                <div class="product-image-placeholder"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                            </td>
                                            <td class="col-name"><?php echo htmlspecialchars($r['product_name']); ?></td>
                                            <td class="col-sku"><?php echo htmlspecialchars($r['sku']); ?></td>
                                            <td class="col-date"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dash-actions">
                    <a href="product_management.php" class="btn-secondary" style="text-decoration:none;"><i class="fa-solid fa-box"></i> Manage Products</a>
                    <a href="stock_management.php" class="btn-secondary" style="text-decoration:none;"><i class="fa-solid fa-warehouse"></i> Manage Stock</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
