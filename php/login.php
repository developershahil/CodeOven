<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_authenticated()) {
    header('Location: dashboard.php');
    exit();
}

$login_error = auth_last_error();
$username_value = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf_token'] ?? null)) {
        $login_error = 'Invalid request token.';
    } else {
        csrf_rotate();
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $username_value = $username;

        if (login_user($username, $password)) {
            header('Location: dashboard.php');
            exit;
        }

        if ($login_error === '') {
            $login_error = auth_last_error();
        }
        if ($login_error === '') {
            $login_error = 'Invalid username or password.';
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CodeOven</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="brand">
                <i class="fas fa-lock"></i>
                <h1>CodeOven Login</h1>
            </div>

            <div class="welcome">
                <h2>Welcome Back</h2>
                <p>Sign in to continue</p>
            </div>

            <?php if ($login_error !== ''): ?>
                <div class="error-message"><?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form" novalidate>
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($username_value, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="32" pattern="[A-Za-z0-9_]{3,32}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required maxlength="128">
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot_password.php">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up here</a><br>
                <a href="../index.html" class="btn btn-outline">Go Back</a>
            </div>
        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>
