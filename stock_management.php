<?php
session_start();
require __DIR__ . '/config.php';
// Fetch all products
$sql = "SELECT id, product_name, image_path, stock FROM products ORDER BY product_name ASC";
$result = $conn->query($sql);
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock — Inventory Manager</title>
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
                <a href="stock_management.php" class="nav-item active">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Stock</span>
                </a>
                <a href="index.php" class="nav-item">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title-group">
                        <h1 class="page-title"><i class="fa-solid fa-warehouse"></i> Stock Management</h1>
                        <span class="page-subtitle"><?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?> tracked</span>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message message-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Stock Table -->
                <div class="product-list-section">
                    <div class="section-header">
                        <h2><i class="fa-solid fa-list-check"></i> Inventory Levels</h2>
                    </div>
                    <div class="table-container">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th style="width:52px;"></th>
                                    <th>Product Name</th>
                                    <th class="col-stock">Current Stock</th>
                                    <th style="width:100px; text-align:center;">Adjust</th>
                                    <th style="width:160px;">Set Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="5" class="table-empty">
                                            <i class="fa-solid fa-box-open"></i>
                                            No products to manage. Add products first.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <?php
                                        $stock = (int)$product['stock'];
                                        $stockClass = 'in-stock';
                                        if ($stock === 0) {
                                            $stockClass = 'out-of-stock';
                                        } elseif ($stock < 10) {
                                            $stockClass = 'low-stock';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                            <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-image-thumb">
                                            <?php else: ?>
                                                <div class="product-image-placeholder"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                            </td>
                                            <td class="col-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td class="col-stock">
                                                <span class="stock-badge <?php echo $stockClass; ?>" id="stock-<?php echo $product['id']; ?>"><?php echo $stock; ?></span>
                                            </td>
                                            <td style="text-align:center;">
                                                <div class="stepper-group" style="justify-content:center;">
                                                    <button class="btn-stepper btn-increment" type="button" onclick="updateStock(<?php echo $product['id']; ?>, 1)" data-product-id="<?php echo $product['id']; ?>" title="Increment">+</button>
                                                    <button class="btn-stepper btn-decrement" type="button" onclick="updateStock(<?php echo $product['id']; ?>, -1)" data-product-id="<?php echo $product['id']; ?>" title="Decrement">−</button>
                                                </div>
                                            </td>
                                            <td>
                                                <form class="inline-set-stock-form" onsubmit="setStock(event, <?php echo $product['id']; ?>)">
                                                    <input type="number" id="stock-input-<?php echo $product['id']; ?>" min="0" value="<?php echo (int)$product['stock']; ?>" class="stock-qty-input" aria-label="Set stock for <?php echo htmlspecialchars($product['product_name']); ?>">
                                                    <button type="submit" class="btn-secondary" style="font-size:0.78rem;padding:6px 11px;">Set</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Function to show a temporary toast message
        function showMessage(message, isSuccess = true) {
            const messageDiv = document.createElement('div');
            messageDiv.className = isSuccess ? 'success-message' : 'message message-error';
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '72px';
            messageDiv.style.right = '20px';
            messageDiv.style.zIndex = '10001';
            messageDiv.style.minWidth = '280px';
            messageDiv.style.maxWidth = '380px';
            messageDiv.style.boxShadow = '0 8px 24px rgba(0,0,0,0.4)';
            messageDiv.style.transition = 'opacity 0.3s';
            messageDiv.innerHTML = '<i class="fa-solid fa-' + (isSuccess ? 'circle-check' : 'circle-exclamation') + '"></i> ' + message;
            document.body.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => messageDiv.remove(), 300);
            }, 2800);
        }

        // Function to update stock badge color
        function updateStockBadge(productId, stock) {
            const badge = document.getElementById('stock-' + productId);
            if (badge) {
                badge.textContent = stock;
                // Update badge class based on stock level
                badge.className = 'stock-badge';
                if (stock == 0) {
                    badge.classList.add('out-of-stock');
                } else if (stock < 10) {
                    badge.classList.add('low-stock');
                } else {
                    badge.classList.add('in-stock');
                }
            }
        }

        // Function to increment/decrement stock
        function updateStock(productId, change) {
            const formData = new FormData();
            formData.append('id', productId);
            formData.append('change', change);

            fetch('update_stock.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStockBadge(productId, data.stock);
                    // Update the input field value as well
                    const input = document.getElementById('stock-input-' + productId);
                    if (input) {
                        input.value = data.stock;
                    }
                    showMessage(data.message, true);
                } else {
                    showMessage(data.message, false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while updating stock.', false);
            });
        }

        // Function to set stock to a specific value
        function setStock(event, productId) {
            event.preventDefault();
            const input = document.getElementById('stock-input-' + productId);
            const newStock = parseInt(input.value) || 0;

            const formData = new FormData();
            formData.append('id', productId);
            formData.append('set_stock', newStock);

            fetch('update_stock.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStockBadge(productId, data.stock);
                    showMessage(data.message, true);
                } else {
                    showMessage(data.message, false);
                    // Reset input to current stock value
                    input.value = document.getElementById('stock-' + productId).textContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while setting stock.', false);
                // Reset input to current stock value
                input.value = document.getElementById('stock-' + productId).textContent;
            });
        }
    </script>
</body>
</html>
