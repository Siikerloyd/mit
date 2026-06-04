<?php
// Step 1: Start session for messages
session_start();

// Step 2: Connect to the database
require __DIR__ . '/config.php';

// Step 3: Check if the form was submitted (POST method)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, redirect back to login page
    header('Location: login.php');
    exit;
}

// Step 4: Get the form data and clean it up
$username = trim($_POST['username'] ?? '');  // Remove extra spaces
$email = trim($_POST['email'] ?? '');        // Remove extra spaces
$password = $_POST['password'] ?? '';         // Get password

// Step 5: Validate the input (check if fields are empty)
$errors = [];

if (empty($username)) {
    $errors[] = "Username is required";
}

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Check if email format is valid
    $errors[] = "Invalid email format";
}

if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 6) {
    // Password should be at least 6 characters
    $errors[] = "Password must be at least 6 characters";
}

// Step 6: If there are errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode('. ', $errors); // Join all errors
    header('Location: login.php');
    exit;
}

// Step 7: Hash the password (NEVER store plain text passwords!)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Step 8: Prepare SQL statement to insert user into database
// The ? marks are placeholders for security (prevents SQL injection)
$sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

// Check if prepare was successful
if (!$stmt) {
    $_SESSION['error'] = "Database error. Please try again.";
    header('Location: login.php');
    exit;
}

// Step 9: Bind the variables to the placeholders
// 'sss' means: string, string, string (3 strings)
$stmt->bind_param('sss', $username, $email, $passwordHash);

// Step 10: Execute the statement (actually insert into database)
if ($stmt->execute()) {
    // Success! User was created
    $_SESSION['success'] = "Account created successfully! Please login.";
    header('Location: login.php');
    exit;
} else {
    // Check what kind of error happened
    if ($conn->errno === 1062) {
        // Error 1062 = Duplicate entry (username or email already exists)
        $_SESSION['error'] = "Username or email already taken!";
    } else {
        // Some other database error
        $_SESSION['error'] = "Registration failed. Please try again.";
    }
    
    // Go back to login page with error message
    header('Location: login.php');
    exit;
}

// Step 11: Clean up (close the statement and connection)
$stmt->close();
$conn->close();
?>

