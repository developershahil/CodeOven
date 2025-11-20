<?php
// verify_otp.php
date_default_timezone_set('Asia/Kolkata'); // 🕒 Gujarat timezone

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Safe session start
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Show errors in development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Make sure email is available
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = trim($_SESSION['reset_email']);
$message = '';

// Determine DB mode
$db_mode = null;
if (isset($pdo) && $pdo instanceof PDO) {
    $db_mode = 'pdo';
} elseif (isset($conn) && is_object($conn)) {
    $db_mode = 'mysqli';
}

// POST handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($input_otp === '' || $new_password === '') {
        $message = "Please enter OTP and a new password.";
    } else {
        $user = false;

        if ($db_mode === 'pdo') {
            $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        } elseif ($db_mode === 'mysqli') {
            $safeEmail = mysqli_real_escape_string($conn, $email);
            $res = mysqli_query($conn, "SELECT * FROM tbl_users WHERE email = '$safeEmail' LIMIT 1");
            if ($res) $user = mysqli_fetch_assoc($res);
        } else {
            $message = "Database connection not found.";
        }

        if (!$user) {
            $message = "No account found for this email.";
        } else {
            $db_otp = isset($user['otp_code']) ? trim((string)$user['otp_code']) : '';
            $db_expiry = isset($user['otp_expiry']) ? $user['otp_expiry'] : null;

            // ✅ Convert expiry to timestamp based on local timezone
            $is_not_expired = false;
            if ($db_expiry) {
                $expiry_ts = strtotime($db_expiry . ' +5 hours +30 minutes'); // adjust UTC → IST
                $now_ts = time();
                $is_not_expired = ($expiry_ts !== false && $expiry_ts >= $now_ts);
            }

            $otp_matches = ($db_otp !== '' && hash_equals($db_otp, $input_otp));

            if ($otp_matches && $is_not_expired) {
                $hash = password_hash($new_password, PASSWORD_BCRYPT);

                if ($db_mode === 'pdo') {
                    $update = $pdo->prepare("UPDATE tbl_users SET password_hash = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
                    $update->execute([$hash, $email]);
                } else {
                    $safeHash = mysqli_real_escape_string($conn, $hash);
                    $safeEmail = mysqli_real_escape_string($conn, $email);
                    mysqli_query($conn, "UPDATE tbl_users SET password_hash = '$safeHash', otp_code = NULL, otp_expiry = NULL WHERE email = '$safeEmail'");
                }

                unset($_SESSION['reset_email'], $_SESSION['debug_otp']);
                $message = "✅ Password updated successfully. <a href='login.php'>Login now</a>";
            } else {
                $message = "Invalid or expired OTP.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="container">
    <div class="login-card">
        <h2>Verify OTP</h2>
        <p>Enter the OTP sent to your email and set a new password</p>

        <form method="POST" class="login-form" autocomplete="off">
            <div class="form-group">
                <label for="otp">OTP</label>
                <input type="text" id="otp" name="otp" placeholder="Enter OTP" required>
            </div>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
            </div>
            <button type="submit" class="login-btn">Reset Password</button>
        </form>

        <?php if ($message): ?>
            <p style="text-align:center; color:<?php echo (strpos($message, '✅') === 0 ? 'green' : 'red'); ?>;">
                <?= $message ?>
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
