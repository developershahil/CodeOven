<?php
declare(strict_types=1);

function workspace_root_path(): string
{
    $configured = getenv('WORKSPACE_ROOT');
    if (is_string($configured) && $configured !== '') {
        return rtrim($configured, DIRECTORY_SEPARATOR);
    }

    return dirname(__DIR__) . '/storage/workspaces';
}

function ensure_workspace_root(): void
{
    $root = workspace_root_path();
    if (!is_dir($root)) {
        mkdir($root, 0700, true);
    }
}

function user_workspace_path(int $userId): string
{
    ensure_workspace_root();
    return workspace_root_path() . DIRECTORY_SEPARATOR . $userId;
}

function ensure_user_workspace(int $userId): string
{
    $path = user_workspace_path($userId);
    if (!is_dir($path)) {
        mkdir($path, 0700, true);
    }
    @chmod($path, 0700);
    return $path;
}

function sanitize_project_name(string $projectName): string
{
    $projectName = trim($projectName);
    $projectName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $projectName) ?? '';
    $projectName = trim($projectName, '._-');

    if ($projectName === '') {
        throw new InvalidArgumentException('Invalid project name.');
    }

    return $projectName;
}

function user_project_path(int $userId, string $projectName): string
{
    $safe = sanitize_project_name($projectName);
    return ensure_user_workspace($userId) . DIRECTORY_SEPARATOR . $safe;
}

function ensure_user_project_dir(int $userId, string $projectName): string
{
    $path = user_project_path($userId, $projectName);
    if (!is_dir($path)) {
        mkdir($path, 0700, true);
    }
    @chmod($path, 0700);
    return $path;
}

function write_project_files(int $userId, string $projectName, string $html, string $css, string $js): void
{
    $dir = ensure_user_project_dir($userId, $projectName);

    file_put_contents($dir . '/index.html', $html);
    file_put_contents($dir . '/style.css', $css);
    file_put_contents($dir . '/script.js', $js);

    @chmod($dir . '/index.html', 0600);
    @chmod($dir . '/style.css', 0600);
    @chmod($dir . '/script.js', 0600);
}

function read_project_files(int $userId, string $projectName): array
{
    $dir = user_project_path($userId, $projectName);

    if (!is_dir($dir)) {
        return ['html' => '', 'css' => '', 'js' => ''];
    }

    return [
        'html' => is_file($dir . '/index.html') ? (string)file_get_contents($dir . '/index.html') : '',
        'css' => is_file($dir . '/style.css') ? (string)file_get_contents($dir . '/style.css') : '',
        'js' => is_file($dir . '/script.js') ? (string)file_get_contents($dir . '/script.js') : '',
    ];
}

function delete_project_dir(int $userId, string $projectName): void
{
    $dir = user_project_path($userId, $projectName);

    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $full = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_file($full) || is_link($full)) {
            @unlink($full);
        }
    }

    @rmdir($dir);
}

function rename_project_dir(int $userId, string $oldName, string $newName): void
{
    $oldPath = user_project_path($userId, $oldName);
    $newPath = user_project_path($userId, $newName);

    if (!is_dir($oldPath)) {
        throw new RuntimeException('Source project not found.');
    }

    if (is_dir($newPath)) {
        throw new RuntimeException('Target project already exists.');
    }

    if (!@rename($oldPath, $newPath)) {
        throw new RuntimeException('Unable to rename workspace directory.');
    }

    @chmod($newPath, 0700);
}
