<?php
require_once __DIR__ . '/../includes/auth.php';

// If form posted, attempt login using DB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login_user($username, $password)) {
        // redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        $login_error = 'Invalid username or password.';
    }
}
// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Professional App</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="brand">
                <i class="fas fa-lock"></i>
                <h1>SecureLogin</h1>
            </div>
            
            <div class="welcome">
                <h2>Welcome Back</h2>
                <p>Sign in to access your account</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot_password.php" >Forgot password?</a>
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