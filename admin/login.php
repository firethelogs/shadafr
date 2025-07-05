<?php
require_once '../includes/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Debug output
    error_log("Login attempt - Username: $username");

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Get admin user from database
        $stmt = $connection->prepare("SELECT id, username, password_hash FROM admin WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $admin = $result->fetchArray(SQLITE3_ASSOC);

        // Debug output
        error_log("Database query result: " . print_r($admin, true));

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Debug output
            error_log("Password verified successfully");
            
            // Set session and redirect to dashboard
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            // Debug output
            error_log("Password verification failed");
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Shada</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control" 
                       required 
                       value="<?php echo h($username ?? ''); ?>"
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control" 
                       required 
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <p style="margin-top: 20px; text-align: center;">
            <a href="../index.php" style="color: var(--primary-color);">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </a>
        </p>
    </div>

    <script>
        // Focus username field on page load
        document.getElementById('username').focus();
    </script>
</body>
</html>
