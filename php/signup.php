<?php
require_once __DIR__ . '/../includes/auth.php';
// handle signup POST
$signup_success = false;
$signup_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($password !== $confirm) {
        $signup_error = 'Passwords do not match.';
    } else {
        $res = register_user($username, $email, $password);
        if ($res['success']) {
            // After signup, auto-login user
            login_user($username, $password);
            header('Location: dashboard.php');
            exit();
        } else {
            $signup_error = $res['message'] ?? 'Signup failed.';
        }
    }
}
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
            
            <form method="POST" action="signup.php" class="signup-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               class="<?php echo isset($errors['username']) ? 'error' : ''; ?>">
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <div class="field-error"><?php echo $errors['username']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>">
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="field-error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" 
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>">
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="field-error"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" 
                               class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>">
                        <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="field-error"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="agree_terms" id="agree_terms" 
                               <?php echo (isset($_POST['agree_terms']) && $_POST['agree_terms']) ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        I agree to the <a href="#">Terms and Conditions</a>
                    </label>
                    <?php if (isset($errors['agree_terms'])): ?>
                        <div class="field-error"><?php echo $errors['agree_terms']; ?></div>
                    <?php endif; ?>
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