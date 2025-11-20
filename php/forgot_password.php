<?php
date_default_timezone_set('Asia/Kolkata'); // 🕒 Gujarat timezone

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$generated_otp = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if ($email === '') {
        $message = "Please enter your email address.";
    } else {
        $user = false;
        $db_mode = null;

        // Detect DB mode
        if (isset($pdo) && $pdo instanceof PDO) {
            $db_mode = 'pdo';
            $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        } elseif (isset($conn) && is_object($conn)) {
            $db_mode = 'mysqli';
            $safeEmail = mysqli_real_escape_string($conn, $email);
            $res = mysqli_query($conn, "SELECT * FROM tbl_users WHERE email = '$safeEmail' LIMIT 1");
            if ($res) $user = mysqli_fetch_assoc($res);
        }

        if (!$user) {
            $message = "No account found with that email.";
        } else {
            // Generate OTP
            $otp = rand(100000, 999999);
            $generated_otp = $otp;
            $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            // Save OTP and expiry
            if ($db_mode === 'pdo') {
                $update = $pdo->prepare("UPDATE tbl_users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
                $update->execute([$otp, $expiry, $email]);
            } else {
                $safeOtp = mysqli_real_escape_string($conn, $otp);
                $safeExpiry = mysqli_real_escape_string($conn, $expiry);
                $safeEmail = mysqli_real_escape_string($conn, $email);
                mysqli_query($conn, "UPDATE tbl_users SET otp_code = '$safeOtp', otp_expiry = '$safeExpiry' WHERE email = '$safeEmail'");
            }

            $_SESSION['reset_email'] = $email;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
        /* 🌟 Smooth, Animated OTP Popup */
        .otp-modal {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transform: scale(1.05);
            transition: all 0.4s ease;
            z-index: 9999;
        }
        .otp-modal.show {
            visibility: visible;
            opacity: 1;
            transform: scale(1);
        }
        .otp-box {
            background: #fff;
            border-radius: 16px;
            padding: 25px 30px;
            width: 320px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            animation: popIn 0.4s ease forwards;
        }
        @keyframes popIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .otp-box h3 {
            margin-bottom: 10px;
            color: #222;
            font-size: 22px;
        }
        .otp-box p {
            color: #555;
            font-size: 16px;
            margin-bottom: 25px;
        }
        .otp-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .otp-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-card">
        <h2>Forgot Password</h2>
        <p>Enter your registered email to receive an OTP</p>

        <form method="POST" class="login-form" autocomplete="off">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="login-btn">Send OTP</button>
        </form>

        <?php if ($message): ?>
            <p style="color:red;text-align:center;"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- 🌟 OTP Popup Modal -->
<div id="otpModal" class="otp-modal">
    <div class="otp-box">
        <h3>OTP Sent!</h3>
        <p>Your OTP is: <strong id="otpDisplay"></strong></p>
        <button class="otp-btn" id="continueBtn">Continue</button>
    </div>
</div>

<?php if ($generated_otp): ?>
<script>
    // ✅ Show animated OTP popup
    const modal = document.getElementById('otpModal');
    const otpDisplay = document.getElementById('otpDisplay');
    const continueBtn = document.getElementById('continueBtn');
    otpDisplay.textContent = "<?php echo $generated_otp; ?>";
    modal.classList.add('show');

    continueBtn.addEventListener('click', function() {
        modal.classList.remove('show');
        window.location.href = "verify_otp.php";
    });
</script>
<?php endif; ?>
</body>
</html>
