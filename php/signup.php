<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_authenticated()) {
    header('Location: dashboard.php');
    exit();
}

$signup_error = '';
$username_value = '';
$email_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf_token'] ?? null)) {
        $signup_error = 'Invalid request token.';
    } else {
        csrf_rotate();

        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $agreeTerms = isset($_POST['agree_terms']) && $_POST['agree_terms'] === 'on';

        $username_value = $username;
        $email_value = $email;

        if (!$agreeTerms) {
            $signup_error = 'You must accept the terms and conditions.';
        } elseif ($password !== $confirm) {
            $signup_error = 'Passwords do not match.';
        } else {
            $res = register_user($username, $email, $password);
            if (!empty($res['success'])) {
                login_user($username, $password);
                header('Location: dashboard.php');
                exit;
            }
            $signup_error = (string)($res['message'] ?? 'Signup failed.');
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
    <title>Sign Up | CodeOven</title>
    <link rel="stylesheet" href="../css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="signup-card">
            <div class="brand">
                <i class="fas fa-user-plus"></i>
                <h1>Create Account</h1>
            </div>

            <div class="welcome">
                <h2>Join Us Today</h2>
                <p>Create your account to get started</p>
            </div>

            <?php if ($signup_error !== ''): ?>
                <div class="error-message"><?php echo htmlspecialchars($signup_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="signup.php" class="signup-form" novalidate>
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" value="<?php echo htmlspecialchars($username_value, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="32" pattern="[A-Za-z0-9_]{3,32}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email_value, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="255">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required minlength="10" maxlength="128">
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required minlength="10" maxlength="128">
                        <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="agree_terms" id="agree_terms" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#">Terms and Conditions</a>
                    </label>
                </div>

                <button type="submit" class="signup-btn">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a><br>
                <a href="../index.html" class="btn btn-outline">Go Back</a>
            </div>
        </div>
    </div>

    <script src="../js/signup.js"></script>
</body>
</html>
