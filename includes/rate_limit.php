<?php
declare(strict_types=1);

function rate_limit_storage_dir(): string
{
    $dir = sys_get_temp_dir() . '/codeoven-rate-limit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    return $dir;
}

function rate_limit_client_ip(): string
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (is_string($ip) && str_contains($ip, ',')) {
        $parts = explode(',', $ip);
        $ip = trim($parts[0]);
    }
    return is_string($ip) && $ip !== '' ? $ip : 'unknown';
}

function rate_limit_hit(string $key, int $limit, int $windowSeconds): bool
{
    $path = rate_limit_storage_dir() . '/' . hash('sha256', $key) . '.json';
    $now = time();
    $records = [];

    if (is_file($path)) {
        $decoded = json_decode((string)file_get_contents($path), true);
        if (is_array($decoded)) {
            $records = array_values(array_filter($decoded, static function ($ts) use ($now, $windowSeconds) {
                return is_int($ts) && ($now - $ts) < $windowSeconds;
            }));
        }
    }

    if (count($records) >= $limit) {
        file_put_contents($path, json_encode($records), LOCK_EX);
        return true;
    }

    $records[] = $now;
    file_put_contents($path, json_encode($records), LOCK_EX);
    return false;
}
