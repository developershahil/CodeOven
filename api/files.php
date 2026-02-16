<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/workspace.php';

$userId = api_require_auth();
api_require_csrf_for_write();

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$projectName = trim((string)($_GET['project'] ?? $_POST['project'] ?? ''));

switch ($method) {
    case 'GET':
        if ($projectName === '') {
            $stmt = $pdo->prepare('SELECT file_name AS project_name, created_at, updated_at FROM tbl_files WHERE user_id = ? ORDER BY updated_at DESC');
            $stmt->execute([$userId]);
            api_json(['success' => true, 'files' => $stmt->fetchAll()]);
        }

        $stmt = $pdo->prepare('SELECT file_name FROM tbl_files WHERE user_id = ? AND file_name = ? LIMIT 1');
        $stmt->execute([$userId, $projectName]);
        if (!$stmt->fetch()) {
            api_json(['success' => false, 'message' => 'Project not found'], 404);
        }

        api_json(['success' => true, 'project' => $projectName, 'file' => read_project_files($userId, $projectName)]);
        break;

    case 'POST':
        $html = (string)($_POST['html'] ?? '');
        $css = (string)($_POST['css'] ?? '');
        $js = (string)($_POST['js'] ?? '');

        if ($projectName === '') {
            api_json(['success' => false, 'message' => 'project required'], 422);
        }

        $safeProjectName = sanitize_project_name($projectName);
        write_project_files($userId, $safeProjectName, $html, $css, $js);

        $stmt = $pdo->prepare('INSERT INTO tbl_files (user_id, file_name, file_type, file_content, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())
                               ON DUPLICATE KEY UPDATE updated_at = NOW()');
        $stmt->execute([$userId, $safeProjectName, 'html', '']);

        api_json(['success' => true, 'project' => $safeProjectName], 201);
        break;

    case 'PATCH':
        $oldName = trim((string)($_POST['old_name'] ?? ''));
        $newName = trim((string)($_POST['new_name'] ?? ''));

        if ($oldName === '' || $newName === '') {
            api_json(['success' => false, 'message' => 'old_name and new_name required'], 422);
        }

        $safeOld = sanitize_project_name($oldName);
        $safeNew = sanitize_project_name($newName);

        rename_project_dir($userId, $safeOld, $safeNew);

        $stmt = $pdo->prepare('UPDATE tbl_files SET file_name = ?, updated_at = NOW() WHERE user_id = ? AND file_name = ?');
        $stmt->execute([$safeNew, $userId, $safeOld]);

        api_json(['success' => true, 'old_name' => $safeOld, 'new_name' => $safeNew]);
        break;

    case 'DELETE':
        if ($projectName === '') {
            api_json(['success' => false, 'message' => 'project required'], 422);
        }

        $safeProjectName = sanitize_project_name($projectName);
        delete_project_dir($userId, $safeProjectName);

        $stmt = $pdo->prepare('DELETE FROM tbl_files WHERE user_id = ? AND file_name = ?');
        $stmt->execute([$userId, $safeProjectName]);

        api_json(['success' => true]);
        break;

    default:
        api_json(['success' => false, 'message' => 'Method not allowed'], 405);
}
