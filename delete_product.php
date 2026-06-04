<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product_management.php');
    exit;
}
require __DIR__ . '/config.php';
$product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$product_id) {
    $_SESSION['error'] = 'Invalid product!';
    header('Location: product_management.php');
    exit;
}
// Find and delete image if exists
$image_path = null;
$stmt = $conn->prepare('SELECT image_path FROM products WHERE id = ?');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$stmt->bind_result($image_path);
$stmt->fetch();
$stmt->close();
if ($image_path && file_exists($image_path)) {
    @unlink($image_path);
}
// Delete product
$stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
$stmt->bind_param('i', $product_id);
if ($stmt->execute()) {
    $_SESSION['success'] = 'Product deleted successfully!';
} else {
    $_SESSION['error'] = 'Failed to delete product.';
}
$stmt->close();
$conn->close();
header('Location: product_management.php');
exit;
