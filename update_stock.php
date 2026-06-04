<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

require __DIR__ . '/config.php';
$product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

// Handle set stock
if (isset($_POST['set_stock'])) {
    $new_stock = max(0, intval($_POST['set_stock']));
    $stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
    $stmt->bind_param('ii', $new_stock, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Stock set successfully!', 'stock' => $new_stock]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to set stock.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Handle increment/decrement
$change = isset($_POST['change']) ? intval($_POST['change']) : 0;
$stmt = $conn->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$stmt->bind_result($current_stock);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$new_stock = $current_stock + $change;
if ($new_stock < 0) $new_stock = 0;

$stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
$stmt->bind_param('ii', $new_stock, $product_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Stock updated successfully!', 'stock' => $new_stock]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update stock.']);
}

$stmt->close();
$conn->close();
exit;
