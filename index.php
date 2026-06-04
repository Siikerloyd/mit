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
    <title>Dashboard - Native PHP App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/product_management.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <img src="assets/logo3.png" alt="Logo" class="logo">
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
                <a href="product_management.php" class="nav-item">
                    <i class="fa-solid fa-box"></i>
                    <span>Product Management</span>
                </a>
                <a href="stock_management.php" class="nav-item">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Stock Management</span>
                </a>
                <a href="index.php" class="nav-item active">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard & Reports</span>
                </a>
            </nav>
        </aside>
        <main class="main-content">
            <div class="container">
                <h1 class="dashboard-title">Dashboard</h1>
                <div style="display:flex; gap:24px; margin-bottom:24px; flex-wrap:wrap;">
                    <div style="background:#0ea5e9;color:white;padding:24px 32px;border-radius:10px;min-width:180px;flex:1;">
                        <div style="font-size:1.9rem;font-weight:bold;">
                            <?php echo (int)$stats['total_products']; ?>
                        </div>
                        <div>Total Products</div>
                    </div>
                    <div style="background:#38bdf8;color:#0e172a;padding:24px 32px;border-radius:10px;min-width:180px;flex:1;">
                        <div style="font-size:1.9rem;font-weight:bold;">
                            <?php echo (int)$stats['total_stock']; ?>
                        </div>
                        <div>Total Stock</div>
                    </div>
                    <div style="background:#fbbf24;color:#7c510e;padding:24px 32px;border-radius:10px;min-width:180px;flex:1;">
                        <div style="font-size:1.9rem;font-weight:bold;">
                            <?php echo (int)$stats['low_stock']; ?>
                        </div>
                        <div>Low Stock (&lt; 10)</div>
                    </div>
                    <div style="background:#ef4444;color:white;padding:24px 32px;border-radius:10px;min-width:180px;flex:1;">
                        <div style="font-size:1.9rem;font-weight:bold;">
                            <?php echo (int)$stats['out_of_stock']; ?>
                        </div>
                        <div>Out of Stock</div>
                    </div>
                </div>
                <!-- Progress bar (stock status distribution) -->
                <div style="margin-bottom:24px;">
                <?php
                $tp = (int)$stats['total_products'] ?: 1;
                $low = (int)$stats['low_stock'];
                $out = (int)$stats['out_of_stock'];
                $good = $tp - $low - $out;
                $goodPct = round(100 * $good / $tp);
                $lowPct = round(100 * $low / $tp);
                $outPct = 100 - $goodPct - $lowPct;
                ?>
                    <div style="margin-bottom: 6px;font-size:1rem;font-weight:500;color:#334155;">Stock Distribution</div>
                    <div style="background:#e5e7eb;border-radius:8px;height:28px;width:100%;overflow:hidden;display:flex;">
                        <div style="background:#38bdf8;width:<?php echo $goodPct; ?>%;height:100%" title="In Stock (<?php echo $goodPct; ?>%)"></div>
                        <div style="background:#fbbf24;width:<?php echo $lowPct; ?>%;height:100%" title="Low Stock (<?php echo $lowPct; ?>%)"></div>
                        <div style="background:#ef4444;width:<?php echo $outPct; ?>%;height:100%" title="Out of Stock (<?php echo $outPct; ?>%)"></div>
                    </div>
                    <div style="font-size:.93rem;color:#64748b;margin-top:6px;display:flex;gap:18px;align-items:center;">
                        <span style="display:inline-flex;align-items:center;gap:5px;"><span style="display:inline-block;width:15px;height:15px;background:#38bdf8;margin-right:4px;border-radius:2px;"></span>In Stock</span>
                        <span style="display:inline-flex;align-items:center;gap:5px;"><span style="display:inline-block;width:15px;height:15px;background:#fbbf24;margin-right:4px;border-radius:2px;"></span>Low Stock</span>
                        <span style="display:inline-flex;align-items:center;gap:5px;"><span style="display:inline-block;width:15px;height:15px;background:#ef4444;margin-right:4px;border-radius:2px;"></span>Out of Stock</span>
                    </div>
                </div>
                <!-- Recent products -->
                <h2 style="margin-bottom:10px; font-size:1.3rem;">Recent Products</h2>
                <div class="table-container" style="margin-bottom:34px;">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProducts as $r): ?>
                                <tr>
                                    <td>
                                    <?php if (!empty($r['image_path']) && file_exists($r['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($r['image_path']); ?>" alt="<?php echo htmlspecialchars($r['product_name']); ?>" class="product-image-thumb">
                                    <?php else: ?>
                                        <div class="product-image-placeholder"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($r['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['sku']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Shortcuts -->
                <div style="display:flex; gap:20px;">
                    <a href="add_product.php" class="btn-primary" style="text-decoration:none;"><i class="fa-solid fa-plus"></i> Add New Product</a>
                    <a href="product_management.php" class="btn-secondary" style="text-decoration:none;">Go to Products</a>
                    <a href="stock_management.php" class="btn-secondary" style="text-decoration:none;">Go to Stock</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
