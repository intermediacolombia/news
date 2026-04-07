<?php
/**
 * Sistema de actualización silenciosa
 * Verifica si hay nuevas versiones en GitHub y puede actualizar en segundo plano
 */

define('GITHUB_REPO', 'intermediacolombia/news');
define('CURRENT_VERSION', '1.0.2');
define('GIT_BIN', 'git');

function get_current_git_hash() {
    if (is_dir(__DIR__ . '/../../.git')) {
        $hash = trim(shell_exec('cd ' . __DIR__ . '/../.. && ' . GIT_BIN . ' rev-parse HEAD 2>/dev/null'));
        return $hash ?: 'unknown';
    }
    return 'unknown';
}

function get_local_version() {
    $version_file = __DIR__ . '/cache/version.json';
    if (file_exists($version_file)) {
        return json_decode(file_get_contents($version_file), true);
    }
    return ['hash' => '', 'updated_at' => ''];
}

function save_local_version($hash, $version) {
    if (!is_dir(__DIR__ . '/cache')) {
        mkdir(__DIR__ . '/cache', 0755, true);
    }
    $version_file = __DIR__ . '/cache/version.json';
    file_put_contents($version_file, json_encode([
        'hash' => $hash,
        'version' => $version,
        'updated_at' => date('Y-m-d H:i:s')
    ]));
}

function check_for_updates() {
    $currentHash = get_current_git_hash();
    $localVersion = get_local_version();
    
    $hasChanges = $currentHash !== $localVersion['hash'];
    
    $result = [
        'current_hash' => $currentHash,
        'saved_hash' => $localVersion['hash'],
        'has_changes' => $hasChanges,
        'update_available' => $hasChanges,
        'checked_at' => date('Y-m-d H:i:s')
    ];
    
    if (!is_dir(__DIR__ . '/cache')) {
        mkdir(__DIR__ . '/cache', 0755, true);
    }
    
    $cache_file = __DIR__ . '/cache/updates.json';
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
        'auto_update_enabled' => true
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
    
    $oldHash = get_current_git_hash();
    $output = shell_exec('cd ' . __DIR__ . '/../.. && ' . GIT_BIN . ' pull origin main 2>&1');
    
    $newHash = get_current_git_hash();
    save_local_version($newHash, CURRENT_VERSION);
    
    $status = [
        'updated_at' => date('Y-m-d H:i:s'),
        'old_hash' => $oldHash,
        'new_hash' => $newHash,
        'output' => $output,
        'success' => true
    ];
    
    save_update_status($status);
    
    return $status;
}

if (php_sapi_name() === 'cli' || !isset($_GET['action'])) {
    $result = check_for_updates();
    echo json_encode($result, JSON_PRETTY_PRINT);
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

if ($_GET['action'] === 'force_check') {
    header('Content-Type: application/json');
    $cache_file = __DIR__ . '/cache/updates.json';
    if (file_exists($cache_file)) unlink($cache_file);
    $result = check_for_updates();
    echo json_encode($result);
    exit;
}

if ($_GET['action'] === 'reset') {
    header('Content-Type: application/json');
    $version_file = __DIR__ . '/cache/version.json';
    $status_file = __DIR__ . '/cache/update_status.json';
    if (file_exists($version_file)) unlink($version_file);
    if (file_exists($status_file)) unlink($status_file);
    $currentHash = get_current_git_hash();
    save_local_version('', CURRENT_VERSION);
    echo json_encode(['success' => true, 'message' => 'Estado reseteado', 'hash' => $currentHash]);
    exit;
}

if ($_GET['action'] === 'update' && $_GET['key'] === 'autoupdate') {
    header('Content-Type: application/json');
    
    $status = get_update_status();
    if (!$status['auto_update_enabled']) {
        echo json_encode(['success' => false, 'message' => 'Auto-update disabled']);
        exit;
    }
    
    $result = perform_silent_update();
    echo json_encode($result);
    exit;
}
