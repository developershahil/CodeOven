<?php
$isAuthenticated = isset($_SESSION['user_id']);
?>
<header class="top-header">
    <?php if ($isAuthenticated): ?>
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="header-actions">
        <button id="theme-toggle" class="icon-btn">🌙</button>
    </div>
    <?php endif; ?>
</header>
