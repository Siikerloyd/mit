<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Connect to database and fetch products
require __DIR__ . '/config.php';

// PHP filter for search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $sql = "SELECT id, product_name, sku, price, stock, description, image_path, created_at FROM products WHERE product_name LIKE ? OR sku LIKE ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
} else {
    $sql = "SELECT id, product_name, sku, price, stock, description, image_path, created_at FROM products ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

$viewProduct = null;
if (isset($_GET['view'])) {
    foreach ($products as $prod) {
        if ($prod['id'] == $_GET['view']) {
            $viewProduct = $prod;
            break;
        }
    }
}

// Get product data for editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_sql = "SELECT id, product_name, sku, price, stock, description, image_path FROM products WHERE id = ?";
    $edit_stmt = $conn->prepare($edit_sql);
    if ($edit_stmt) {
        $edit_stmt->bind_param('i', $edit_id);
        $edit_stmt->execute();
        $edit_result = $edit_stmt->get_result();
        if ($edit_result && $edit_result->num_rows > 0) {
            $editProduct = $edit_result->fetch_assoc();
        }
        $edit_stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Native PHP App</title>
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
                <a href="product_management.php" class="nav-item active">
                    <i class="fa-solid fa-box"></i>
                    <span>Product Management</span>
                </a>
                <a href="stock_management.php" class="nav-item">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Stock Management</span>
                </a>
                <a href="index.php" class="nav-item">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard & Reports</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <div class="page-header">
                <h1 class="page-title">
                    <i class="fa-solid fa-box"></i> Product Management
                </h1>
                
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
                    <button class="btn-primary" onclick="showAddForm()" style="display: <?php echo $editProduct ? 'none' : 'inline-flex'; ?>;">
                        <i class="fa-solid fa-plus"></i> Add New Product
                    </button>
                </div>

                <!-- Add Product Form -->
                <div id="add-product-form" class="product-form" style="display: <?php echo $editProduct ? 'none' : 'none'; ?>;">
                    <div class="form-header">
                        <h2><i class="fa-solid fa-plus-circle"></i> Add New Product</h2>
                        <button class="btn-close" onclick="hideAddForm()">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <form class="form-content" action="add_product.php" method="post" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="product-name">
                                    <i class="fa-solid fa-tag"></i> Product Name
                                </label>
                                <input type="text" id="product-name" name="product_name" required>
                            </div>
                            <div class="form-group">
                                <label for="product-sku">
                                    <i class="fa-solid fa-barcode"></i> SKU
                                </label>
                                <input type="text" id="product-sku" name="sku" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="product-price">
                                    <i class="fa-solid fa-dollar-sign"></i> Price
                                </label>
                                <input type="number" id="product-price" name="price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="product-stock">
                                    <i class="fa-solid fa-cubes"></i> Stock Quantity
                                </label>
                                <input type="number" id="product-stock" name="stock" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="product-description">
                                <i class="fa-solid fa-align-left"></i> Description
                            </label>
                            <textarea id="product-description" name="description" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="product-image">
                                <i class="fa-solid fa-image"></i> Product Image
                            </label>
                            <div class="image-upload">
                                <input type="file" id="product-image" name="image" accept="image/*">
                                <label for="product-image" class="upload-label">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Upload Product Image</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="hideAddForm()">Cancel</button>
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-save"></i> Save Product
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Product List -->
                <div class="product-list-section">
                    <div class="section-header">
                        <h2><i class="fa-solid fa-list"></i> Product List</h2>
                        <form method="get" class="search-box" style="margin:0;">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." autocomplete="off">
                        </form>
                    </div>

                    <div class="table-container">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: rgba(255, 255, 255, 0.6);">
                                            <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                            No products found. Add your first product to get started!
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                                         class="product-image-thumb">
                                                <?php else: ?>
                                                    <div class="product-image-placeholder">
                                                        <i class="fa-solid fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                                            <td>
                                                <?php 
                                                $stock = (int)$product['stock'];
                                                $stockClass = 'in-stock';
                                                if ($stock === 0) {
                                                    $stockClass = 'out-of-stock';
                                                } elseif ($stock < 10) {
                                                    $stockClass = 'low-stock';
                                                }
                                                ?>
                                                <span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stock; ?></span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a class="btn-view" href="?view=<?php echo $product['id']; ?>" title="View Details">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <button class="btn-edit" onclick="editProduct(<?php echo $product['id']; ?>)" title="Edit">
                                                        <i class="fa-solid fa-pencil"></i>
                                                    </button>
                                                    <form action="delete_product.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" class="btn-delete" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Product Details Modal (will show when view is clicked) -->
                <?php if ($viewProduct): ?>
                <div id="product-details-modal" class="modal" style="display:flex;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><i class="fa-solid fa-info-circle"></i> Product Details</h2>
                            <a class="btn-close" href="product_management.php" title="Close"><i class="fa-solid fa-times"></i></a>
                        </div>
                        <div class="modal-body">
                            <div class="detail-grid">
                                <div class="detail-item full-width image-section">
                                    <label>Product Image:</label>
                                    <div class="product-image-large">
                                    <?php if (!empty($viewProduct['image_path']) && file_exists($viewProduct['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($viewProduct['image_path']); ?>" alt="<?php echo htmlspecialchars($viewProduct['product_name']); ?>">
                                    <?php else: ?>
                                        <div class="product-image-placeholder-large">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <label><i class="fa-solid fa-tag"></i> Product Name:</label>
                                    <span><?php echo htmlspecialchars($viewProduct['product_name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label><i class="fa-solid fa-barcode"></i> SKU:</label>
                                    <span><?php echo htmlspecialchars($viewProduct['sku']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label><i class="fa-solid fa-dollar-sign"></i> Price:</label>
                                    <span style="font-size: 1.3rem; font-weight: 600; color: #38bdf8;">$<?php echo number_format($viewProduct['price'],2); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label><i class="fa-solid fa-cubes"></i> Stock Quantity:</label>
                                    <span class="stock-badge <?php echo ($viewProduct['stock'] == 0) ? 'out-of-stock' : (($viewProduct['stock'] < 10) ? 'low-stock' : 'in-stock'); ?>">
                                        <?php echo (int)$viewProduct['stock']; ?>
                                    </span>
                                </div>
                                <div class="detail-item full-width">
                                    <label><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <p style="margin-top: 8px; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($viewProduct['description'] ?: 'No description available.')); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a class="btn-primary" href="?edit=<?php echo $viewProduct['id']; ?>">
                                <i class="fa-solid fa-pencil"></i> Edit Product
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Edit Product Form (will show when edit is clicked) -->
                <div id="edit-product-form" class="product-form" style="display: <?php echo $editProduct ? 'block' : 'none'; ?>;">
                    <div class="form-header">
                        <h2><i class="fa-solid fa-pencil"></i> Edit Product</h2>
                        <button class="btn-close" onclick="hideEditForm()">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <?php if ($editProduct): ?>
                    <form class="form-content" id="edit-product-form-tag" action="update_product.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit-product-id" value="<?php echo htmlspecialchars($editProduct['id']); ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit-product-name">
                                    <i class="fa-solid fa-tag"></i> Product Name
                                </label>
                                <input type="text" id="edit-product-name" name="product_name" value="<?php echo htmlspecialchars($editProduct['product_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-product-sku">
                                    <i class="fa-solid fa-barcode"></i> SKU
                                </label>
                                <input type="text" id="edit-product-sku" name="sku" value="<?php echo htmlspecialchars($editProduct['sku']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit-product-price">
                                    <i class="fa-solid fa-dollar-sign"></i> Price
                                </label>
                                <input type="number" id="edit-product-price" name="price" step="0.01" value="<?php echo htmlspecialchars($editProduct['price']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-product-stock">
                                    <i class="fa-solid fa-cubes"></i> Stock Quantity
                                </label>
                                <input type="number" id="edit-product-stock" name="stock" value="<?php echo htmlspecialchars($editProduct['stock']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit-product-description">
                                <i class="fa-solid fa-align-left"></i> Description
                            </label>
                            <textarea id="edit-product-description" name="description" rows="4"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="edit-product-image">
                                <i class="fa-solid fa-image"></i> Product Image
                            </label>
                            <?php if (!empty($editProduct['image_path']) && file_exists($editProduct['image_path'])): ?>
                                <div style="margin-bottom: 15px;">
                                    <label style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7); margin-bottom: 8px; display: block;">Current Image:</label>
                                    <img src="<?php echo htmlspecialchars($editProduct['image_path']); ?>" alt="Current product image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid rgba(56, 189, 248, 0.3); object-fit: contain;">
                                </div>
                            <?php endif; ?>
                            <div class="image-upload">
                                <input type="file" id="edit-product-image" name="image" accept="image/*">
                                <label for="edit-product-image" class="upload-label">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span><?php echo !empty($editProduct['image_path']) ? 'Change Product Image' : 'Upload Product Image'; ?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="hideEditForm()">Cancel</button>
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-save"></i> Update Product
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showAddForm() {
            document.getElementById('add-product-form').style.display = 'block';
            document.getElementById('edit-product-form').style.display = 'none';
        }

        function hideAddForm() {
            document.getElementById('add-product-form').style.display = 'none';
        }

        function editProduct(id) {
            // Hide add form
            document.getElementById('add-product-form').style.display = 'none';
            // Redirect to edit page with product ID
            window.location.href = '?edit=' + id;
        }

        function hideEditForm() {
            document.getElementById('edit-product-form').style.display = 'none';
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                // Will handle deletion via PHP later
                console.log('Delete product:', id);
            }
        }
    </script>
</body>
</html>
