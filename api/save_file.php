<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = current_user_id();
$file_name_input = trim($_POST['file_name'] ?? '');
$html = $_POST['html'] ?? '';
$css = $_POST['css'] ?? '';
$js = $_POST['js'] ?? '';

if ($file_name_input === '') {
    echo json_encode(['success' => false, 'message' => 'File name required.']);
    exit();
}

// --- 🧠 Extract file name (without extension) ---
$path_info = pathinfo($file_name_input);
$base_name = $path_info['filename']; // 'index' from 'index.html'
$ext = strtolower($path_info['extension'] ?? ''); // 'html', 'css', or 'js'

// --- If file type provided in content keys, use those ---
$types = ['html' => $html, 'css' => $css, 'js' => $js];

try {
    foreach ($types as $type => $content) {
        $content = trim($content);

        if ($content === '') {
            continue;
        }

        // 🧩 Ensure base name only is stored (no extension)
        $stmt = $pdo->prepare('SELECT file_id FROM tbl_files WHERE user_id = ? AND file_name = ? AND file_type = ? LIMIT 1');
        $stmt->execute([$user_id, $base_name, $type]);
        $row = $stmt->fetch();

        if ($row) {
            $stmt2 = $pdo->prepare('UPDATE tbl_files SET file_content = ?, updated_at = NOW() WHERE file_id = ?');
            $stmt2->execute([$content, $row['file_id']]);
        } else {
            $stmt2 = $pdo->prepare('INSERT INTO tbl_files (user_id, file_name, file_type, file_content) VALUES (?, ?, ?, ?)');
            $stmt2->execute([$user_id, $base_name, $type, $content]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Saved']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
