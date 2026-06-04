<?php
// Start session to display messages from register.php
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // Connect to database
    require __DIR__ . '/config.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if (empty($username)) {
        $errors[] = "Username is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode('. ', $errors);
        header('Location: login.php');
        exit;
    }

    // Get user from database by username
    $sql = "SELECT id, username, email, password_hash FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error. Please try again.";
        header('Location: login.php');
        exit;
    }

    // Bind username parameter and execute
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists and verify password
    if (!$user) {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: login.php');
        exit;
    }

    // Verify the password using password_verify (NOT password_hash!)
    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: login.php');
        exit;
    }

    // Login successful! Create session
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    // Clean up
    $stmt->close();
    $conn->close();
    
    // Redirect to index.php
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Native PHP App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/login.css">
    <script>
        function toggleView(target) {
            const loginCard = document.getElementById('login-container');
            const registerCard = document.getElementById('register-container');

            if (target === 'register') {
                loginCard.classList.remove('active');
                loginCard.classList.add('inactive');
                registerCard.classList.remove('inactive');
                registerCard.classList.add('active');
            } else {
                registerCard.classList.remove('active');
                registerCard.classList.add('inactive');
                loginCard.classList.remove('inactive');
                loginCard.classList.add('active');
            }
        }

        function showRegister() {
            toggleView('register');
        }

        function showLogin() {
            toggleView('login');
        }
    </script>
</head>
<body>
    <main>
        <div class="auth-wrapper">
            <div id="login-container" class="auth-card active">
                <h1>Welcome back</h1>
                <p class="subtitle">Log in to manage your account and stay up to date.</p>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="message message-success">
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
                
                <form action="login.php" method="post">
                    <div class="field-group">
                        <label for="login-username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="login-username" name="username" required autocomplete="username">
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="login-password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="login-password" name="password" required autocomplete="current-password">
                        </div>
                    </div>
                    <div class="button-row">
                        <button type="submit" class="primary-button"><i class="fa-solid fa-right-to-bracket"></i> Sign in</button>
                        <button type="button" class="secondary-button" onclick="showRegister()"><i class="fa-solid fa-user-plus"></i> Create account</button>
                    </div>
                </form>
            </div>

            <div id="register-container" class="auth-card inactive">
                <h1>Create account</h1>
                <p class="subtitle">Sign up to unlock personalized dashboards and tools.</p>
                <form action="register.php" method="post">
                    <div class="field-group">
                        <label for="register-username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text" id="register-username" name="username" required autocomplete="username">
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="register-email">Email</label>
                        <div class="input-wrapper">
                            <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="register-email" name="email" required autocomplete="email">
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="register-password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="register-password" name="password" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="button-row">
                        <button type="submit" class="primary-button"><i class="fa-solid fa-user-plus"></i> Create account</button>
                        <button type="button" class="secondary-button" onclick="showLogin()"><i class="fa-solid fa-arrow-left"></i> Back to login</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
