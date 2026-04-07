<?php
/**
 * Sistema de actualización automática vía Git
 * Compara el hash local vs origin/main para detectar nuevos pushes
 */

define('GIT_BIN', 'git');
define('REPO_PATH', realpath(__DIR__ . '/../..'));
define('CACHE_DIR', __DIR__ . '/cache');
define('CACHE_FILE', CACHE_DIR . '/updates.json');
define('CACHE_TTL', 300); // 5 minutos

function git_exec($cmd) {
    $prev = getcwd();
    chdir(REPO_PATH);
    shell_exec(GIT_BIN . ' config user.name "intermediacolombia"');
    shell_exec(GIT_BIN . ' config user.email "intermediacolombia@gmail.com"');
    $result = trim(shell_exec(GIT_BIN . ' ' . $cmd . ' 2>&1'));
    chdir($prev);
    return $result;
}

function ensure_cache_dir() {
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }
}

function get_cached_check() {
    if (!file_exists(CACHE_FILE)) return null;
    $data = json_decode(file_get_contents(CACHE_FILE), true);
    if (!$data || !isset($data['_cached_at'])) return null;
    if ((time() - $data['_cached_at']) > CACHE_TTL) return null;
    return $data;
}

function save_cache($data) {
    ensure_cache_dir();
    $data['_cached_at'] = time();
    file_put_contents(CACHE_FILE, json_encode($data));
}

function invalidate_cache() {
    if (file_exists(CACHE_FILE)) unlink(CACHE_FILE);
}

function check_for_updates($force = false) {
    if (!is_dir(REPO_PATH . '/.git')) {
        return ['update_available' => false, 'error' => 'No es un repositorio git válido'];
    }

    if (!$force) {
        $cached = get_cached_check();
        if ($cached) return $cached;
    }

    // Obtener cambios del repositorio remoto
    $fetchOutput = git_exec('fetch origin main 2>&1');
    $fetchError  = (stripos($fetchOutput, 'error') !== false || stripos($fetchOutput, 'fatal') !== false)
                   ? $fetchOutput : null;

    $localHash  = git_exec('rev-parse HEAD');
    $remoteHash = git_exec('rev-parse origin/main');

    $updateAvailable = (
        !empty($localHash) &&
        !empty($remoteHash) &&
        $localHash !== $remoteHash &&
        strpos($localHash, 'fatal') === false &&
        strpos($remoteHash, 'fatal') === false
    );

    $commitsBehind = 0;
    $newCommits    = [];

    if ($updateAvailable) {
        $commitsBehind = (int) git_exec('rev-list --count HEAD..origin/main');
        $log = git_exec('log HEAD..origin/main --pretty=format:"%h|%s|%an|%ar" --no-merges');
        if ($log && strpos($log, 'fatal') === false) {
            foreach (explode("\n", $log) as $line) {
                if (trim($line)) {
                    $parts = explode('|', $line, 4);
                    $newCommits[] = [
                        'hash'    => $parts[0] ?? '',
                        'message' => $parts[1] ?? '',
                        'author'  => $parts[2] ?? '',
                        'date'    => $parts[3] ?? '',
                    ];
                }
            }
        }
    }

    $result = [
        'update_available' => $updateAvailable,
        'local_hash'       => substr($localHash, 0, 8),
        'remote_hash'      => substr($remoteHash, 0, 8),
        'commits_behind'   => $commitsBehind,
        'new_commits'      => $newCommits,
        'checked_at'       => date('Y-m-d H:i:s'),
        'fetch_error'      => $fetchError,
    ];

    save_cache($result);
    return $result;
}

// ============================================================
// ROUTER DE ACCIONES
// ============================================================

if (!isset($_GET['action'])) {
    header('Content-Type: application/json');
    echo json_encode(check_for_updates(true), JSON_PRETTY_PRINT);
    exit;
}

// --- Diagnóstico: ver si git funciona ---
$action = $_GET['action'];

if ($action === 'debug') {
    header('Content-Type: application/json');
    $repoPath = REPO_PATH;
    echo json_encode([
        'repo_path'      => $repoPath,
        'git_version'    => git_exec('--version'),
        'local_head'     => git_exec('rev-parse HEAD'),
        'origin_main'    => git_exec('rev-parse origin/main'),
        'remote_url'     => git_exec('remote get-url origin'),
        'fetch_test'     => git_exec('fetch origin main --dry-run 2>&1'),
        'status'         => git_exec('status --short'),
        'repo_exists'    => is_dir($repoPath . '/.git'),
    ], JSON_PRETTY_PRINT);
    exit;
}

// --- Verificar actualizaciones (usa caché) ---
if ($action === 'check') {
    header('Content-Type: application/json');
    echo json_encode(check_for_updates());
    exit;
}

// --- Forzar verificación limpiando caché ---
if ($action === 'force_check') {
    header('Content-Type: application/json');
    invalidate_cache();
    echo json_encode(check_for_updates(true));
    exit;
}

// --- Actualización con streaming en tiempo real ---
if ($action === 'stream_update') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    header('Transfer-Encoding: chunked');

    if (!is_dir(REPO_PATH . '/.git')) {
        echo "ERROR: No es un repositorio git válido\n";
        exit;
    }

    chdir(REPO_PATH);
    shell_exec(GIT_BIN . ' config user.name "intermediacolombia"');
    shell_exec(GIT_BIN . ' config user.email "intermediacolombia@gmail.com"');

    shell_exec(GIT_BIN . ' fetch origin main 2>&1');
    $process = popen(GIT_BIN . ' reset --hard origin/main 2>&1', 'r');

    if (!$process) {
        echo "ERROR: No se pudo ejecutar git pull\n";
        exit;
    }

    echo "Iniciando actualización desde origin/main...\n\n";
    ob_flush(); flush();

    while (!feof($process)) {
        $line = fgets($process);
        if ($line !== false) {
            echo $line;
            ob_flush(); flush();
        }
        usleep(50000);
    }

    $exitCode = pclose($process);

    echo "\n";
    if ($exitCode === 0) {
        echo "DONE:success\n";

        // Ejecutar reparación automática de BD tras cada actualización
        $dbRepairFile = REPO_PATH . '/admin/inc/db_repair.php';
        if (file_exists($dbRepairFile)) {
            echo "Aplicando cambios de base de datos...\n";
            ob_flush(); flush();
            require_once $dbRepairFile;
            $repairResults = repair_database();
            foreach (array_merge($repairResults['tables'], $repairResults['columns'], $repairResults['permissions']) as $msg) {
                echo "  BD: $msg\n";
            }
            foreach ($repairResults['errors'] as $err) {
                echo "  ERROR BD: $err\n";
            }
            ob_flush(); flush();
        }
    } else {
        echo "DONE:error\n";
    }

    invalidate_cache();
    exit;
}

header('Content-Type: application/json');
echo json_encode(['error' => 'Acción no válida']);
exit;
