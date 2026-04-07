<?php
/**
 * Sistema de actualización silenciosa
 * Verifica si hay nuevas versiones en GitHub y puede actualizar en segundo plano
 */

define('GITHUB_REPO', 'intermediacolombia/news');
define('CURRENT_VERSION', '1.0.0');
define('GIT_BIN', 'git');

function get_latest_version_from_github() {
    $url = 'https://api.github.com/repos/' . GITHUB_REPO . '/releases/latest';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP Update Checker',
        'Accept: application/vnd.github+json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['tag_name'] ?? null;
    }
    
    return null;
}

function get_current_git_hash() {
    if (is_dir(__DIR__ . '/../../.git')) {
        $hash = trim(shell_exec('cd ' . __DIR__ . '/../.. && ' . GIT_BIN . ' rev-parse --short HEAD 2>/dev/null'));
        return $hash ?: 'unknown';
    }
    return 'unknown';
}

function check_for_updates() {
    $cache_file = __DIR__ . '/cache/updates.json';
    $cache_time = 3600;
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        $cached = json_decode(file_get_contents($cache_file), true);
        return $cached;
    }
    
    $latest = get_latest_version_from_github();
    $currentHash = get_current_git_hash();
    
    $result = [
        'current' => CURRENT_VERSION,
        'current_hash' => $currentHash,
        'latest' => $latest,
        'update_available' => version_compare($latest, CURRENT_VERSION) > 0,
        'checked_at' => date('Y-m-d H:i:s')
    ];
    
    if (!is_dir(__DIR__ . '/cache')) {
        mkdir(__DIR__ . '/cache', 0755, true);
    }
    
    file_put_contents($cache_file, json_encode($result));
    
    return $result;
}

function get_update_status() {
    $status_file = __DIR__ . '/cache/update_status.json';
    
    if (file_exists($status_file)) {
        return json_decode(file_get_contents($status_file), true);
    }
    
    return [
        'last_check' => null,
        'update_available' => false,
        'auto_update_enabled' => false
    ];
}

function save_update_status($status) {
    $status_file = __DIR__ . '/cache/update_status.json';
    file_put_contents($status_file, json_encode($status));
}

function perform_silent_update() {
    if (!is_dir(__DIR__ . '/../../.git')) {
        return ['success' => false, 'message' => 'No git repository found'];
    }
    
    $output = shell_exec('cd ' . __DIR__ . '/../.. && ' . GIT_BIN . ' pull origin main 2>&1');
    
    $status = [
        'updated_at' => date('Y-m-d H:i:s'),
        'output' => $output,
        'success' => strpos($output, 'Already up to date') !== false || strpos($output, 'Updating') !== false
    ];
    
    save_update_status($status);
    
    return $status;
}

if (php_sapi_name() === 'cli' || !isset($_GET['action'])) {
    $result = check_for_updates();
    echo json_encode($result);
    exit;
}

if ($_GET['action'] === 'check') {
    header('Content-Type: application/json');
    $result = check_for_updates();
    echo json_encode($result);
    exit;
}

if ($_GET['action'] === 'status') {
    header('Content-Type: application/json');
    $status = get_update_status();
    echo json_encode($status);
    exit;
}

if ($_GET['action'] === 'update' && $_GET['key'] === 'autoupdate') {
    header('Content-Type: application/json');
    $result = perform_silent_update();
    echo json_encode($result);
    exit;
}
