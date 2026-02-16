<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_authenticated()) {
    header('Location: dashboard.php');
    exit();
}

$signup_success = false;
$signup_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf_token'] ?? null)) {
        $signup_error = 'Invalid request token.';
    } else {
        csrf_rotate();
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($signup_error === '' && $password !== $confirm) {
        $signup_error = 'Passwords do not match.';
    } elseif ($signup_error === '') {
        $res = register_user($username, $email, $password);
        if ($res['success']) {
            login_user($username, $password);
            header('Location: dashboard.php');
            exit;
        } else {
            $signup_error = $res['message'] ?? 'Signup failed.';
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
    <title>Sign Up | Professional App</title>
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

            <form method="POST" action="signup.php" class="signup-form">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" 
                               required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" 
                               required>
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
