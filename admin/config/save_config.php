<?php
// ── 1. Sesión PRIMERO, igual que en los demás archivos del admin ──────────
session_start();

require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

// ── 2. Header JSON ANTES de cualquier output ──────────────────────────────
header('Content-Type: application/json; charset=UTF-8');

// ── 3. Verificar sesión manualmente (NO usar restriction.php aquí)
//    restriction.php hace header('Location:...') que rompe la respuesta JSON
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizadooooooooo.']);
    exit;
}

db()->exec("SET NAMES utf8mb4");
db()->exec("SET CHARACTER SET utf8mb4");
db()->exec("SET SESSION collation_connection = utf8mb4_general_ci");

function isValidKey(string $key): bool {
    return (bool) preg_match('/^[a-zA-Z0-9_\-]{1,100}$/', $key);
}

const BLOCKED_KEYS = ['id', 'created_at', 'updated_at', 'setting_name'];

const ENABLED_CONTROLLED = [
    'mail_new_order_message', 'mail_shipped_message', 'mail_delivered_message',
    'ws_new_order_message',   'ws_shipped_message',   'ws_delivered_message',
];

try {

    // 1. ELIMINAR ARCHIVOS (delete_*)
    foreach ($_POST as $key => $val) {
        if (!str_starts_with($key, 'delete_')) continue;
        $settingName = substr($key, strlen('delete_'));
        if (!isValidKey($settingName)) continue;

        $stmt = db()->prepare("SELECT value FROM system_settings WHERE setting_name = ? LIMIT 1");
        $stmt->execute([$settingName]);
        $filePath = $stmt->fetchColumn();

        if ($filePath) {
            $abs = __DIR__ . '/../../' . ltrim($filePath, '/');
            if (file_exists($abs)) unlink($abs);
        }
        db()->prepare("DELETE FROM system_settings WHERE setting_name = ?")->execute([$settingName]);
    }

    // 2. IMÁGENES
    $imageMap = [
        'site_logo'       => ['logo',          'site_logo'],
        'site_favicon'    => ['favicon',        'site_favicon'],
        'banner_inferior' => ['bannerinferior', 'banner_inferior'],
    ];
    $allowedImageExts = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'ico'];
    $uploadDir        = __DIR__ . '/../../public/images/';

    foreach ($imageMap as $fieldName => [$prefix, $settingName]) {
        if (empty($_FILES[$fieldName]['name']) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedImageExts, true)) continue;
        $fileName = $prefix . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadDir . $fileName)) continue;
        $stmt = db()->prepare("INSERT INTO system_settings (setting_name, value, enabled)
            VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP");
        $stmt->execute([$settingName, '/public/images/' . $fileName]);
    }

    // 3. CAMPOS DE TEXTO — AUTOMÁTICO
    $stmt = db()->prepare("INSERT INTO system_settings (setting_name, value, enabled)
        VALUES (:name, :value, :enabled)
        ON DUPLICATE KEY UPDATE value = VALUES(value), enabled = VALUES(enabled), updated_at = CURRENT_TIMESTAMP");

    foreach ($_POST as $key => $rawValue) {
        if (str_starts_with($key, 'delete_'))     continue;
        if (str_ends_with($key, '_enabled'))      continue;
        if (!isValidKey($key))                    continue;
        if (in_array($key, BLOCKED_KEYS, true))   continue;

        $value   = trim((string) $rawValue);
        $enabled = 1;

        if (in_array($key, ENABLED_CONTROLLED, true)) {
            $enabled = isset($_POST[$key . '_enabled']) ? 1 : 0;
        }

        $stmt->execute([':name' => $key, ':value' => $value, ':enabled' => $enabled]);
    }

    // 4. LOG opcional
    if (file_exists(__DIR__ . '/../inc/log_action.php')) {
        require_once __DIR__ . '/../inc/log_action.php';
        log_action('Actualizar Configuraciones',
            json_encode(['accion' => 'Actualizó Configuraciones del Sistema'], JSON_UNESCAPED_UNICODE),
            'Configuraciones');
    }

    echo json_encode(['success' => true, 'message' => 'Configuraciones guardadas correctamente.'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}