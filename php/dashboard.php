<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login_page('login.php');

$username = $_SESSION['username'] ?? 'Guest';
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Editor Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="../codemirror/codemirror-5.65.20/lib/codemirror.css">
    <link rel="stylesheet" href="../codemirror/codemirror-5.65.20/theme/dracula.css">
    <link rel="stylesheet" href="../codemirror/codemirror-5.65.20/theme/eclipse.css">
    <link rel="stylesheet" href="../codemirror/addon/dialog/dialog.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <?php include 'file_expo.php'; ?>
        
        <div class="editor-preview-container">
            <?php include 'editor_area.php'; ?>
            <?php include 'preview.php'; ?>
        </div>
    </div>

    <!-- CodeMirror JS -->
    <script src="../codemirror/codemirror-5.65.20/lib/codemirror.js"></script>
    <script src="../codemirror/codemirror-5.65.20/mode/xml/xml.js"></script>
    <script src="../codemirror/codemirror-5.65.20/mode/css/css.js"></script>
    <script src="../codemirror/codemirror-5.65.20/mode/javascript/javascript.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/edit/closetag.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/edit/matchbrackets.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/hint/show-hint.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/hint/xml-hint.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/hint/html-hint.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/hint/css-hint.js"></script>
    <script src="../codemirror/codemirror-5.65.20/addon/hint/javascript-hint.js"></script>
    <script src="../codemirror/addon/search/search.js"></script>
    <script src="../codemirror/addon/search/searchcursor.js"></script>
    <script src="../codemirror/addon/dialog/dialog.js"></script>

    <script src="../js/dashboard.js"></script>
    <script src="../js/api_integration.js"></script>
</body>
</html>
