<?php
// Step 1: Start session for messages
session_start();

// Step 2: Connect to the database
require __DIR__ . '/config.php';

// Step 3: Check if the form was submitted (POST method)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, redirect back to product management page
    header('Location: product_management.php');
    exit;
}

// Step 4: Get the form data and clean it up
$product_name = trim($_POST['product_name'] ?? '');
$sku = trim($_POST['sku'] ?? '');
$price = trim($_POST['price'] ?? '');
$stock = trim($_POST['stock'] ?? '');
$description = trim($_POST['description'] ?? '');

// Step 5: Validate the input
$errors = [];

if (empty($product_name)) {
    $errors[] = "Product name is required";
}

if (empty($sku)) {
    $errors[] = "SKU is required";
}

if (empty($price)) {
    $errors[] = "Price is required";
} elseif (!is_numeric($price) || $price <= 0) {
    $errors[] = "Price must be a positive number";
}

if (empty($stock)) {
    $errors[] = "Stock quantity is required";
} elseif (!is_numeric($stock) || $stock < 0) {
    $errors[] = "Stock must be a non-negative number";
}

// Step 6: If there are errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode('. ', $errors);
    header('Location: product_management.php');
    exit;
}

// Step 7: Handle image upload (if provided)
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/products/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['image'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Validate file type
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error'] = "Invalid image format. Allowed: JPG, PNG, GIF, WEBP";
        header('Location: product_management.php');
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5000000) {
        $_SESSION['error'] = "Image size must be less than 5MB";
        header('Location: product_management.php');
        exit;
    }
    
    // Generate unique filename
    $unique_filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $image_path = $target_path;
    } else {
        $_SESSION['error'] = "Failed to upload image. Please try again.";
        header('Location: product_management.php');
        exit;
    }
}

// Step 8: Check if SKU already exists
$check_sql = "SELECT id FROM products WHERE sku = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('s', $sku);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "SKU already exists. Please use a different SKU.";
    $check_stmt->close();
    $conn->close();
    header('Location: product_management.php');
    exit;
}
$check_stmt->close();

// Step 9: Prepare SQL statement to insert product into database
$sql = "INSERT INTO products (product_name, sku, price, stock, description, image_path) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Database error. Please try again.";
    $conn->close();
    header('Location: product_management.php');
    exit;
}

// Step 10: Convert data types and handle optional fields
$price = (float)$price;  // Convert to float for DECIMAL
$stock = (int)$stock;     // Convert to integer
$description = empty($description) ? null : $description;  // Set to NULL if empty
$image_path = $image_path ?? null;  // Set to NULL if no image uploaded

// 'ssdiss' means: string, string, double(decimal), integer, string, string
$stmt->bind_param('ssdiss', $product_name, $sku, $price, $stock, $description, $image_path);

// Step 11: Execute the statement (actually insert into database)
if ($stmt->execute()) {
    // Success! Product was created
    $_SESSION['success'] = "Product added successfully!";
    header('Location: product_management.php');
    exit;
} else {
    // Database error
    $_SESSION['error'] = "Failed to add product. Please try again.";
    header('Location: product_management.php');
    exit;
}

// Step 12: Clean up (close the statement and connection)
$stmt->close();
$conn->close();
?>
