<?php
require_once __DIR__ . '/../login/session.php';
header('Content-Type: application/json');

try {
    db()->exec("SET NAMES utf8mb4");

    // === GUARDAR ARCHIVOS ===
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $files = ['system_logo', 'system_favicon'];
    foreach ($files as $fileKey) {
        if (!empty($_FILES[$fileKey]['name'])) {
            $ext      = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
            $filename = $fileKey . '_' . time() . '.' . $ext;
            $dest     = $uploadDir . $filename;
            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
                $stmt = db()->prepare("
                    INSERT INTO system_settings (setting_name, value, enabled)
                    VALUES (:name, :value, 1)
                    ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([':name' => $fileKey, ':value' => $filename]);
            }
        }
    }

    // === GUARDAR CAMPOS DE TEXTO ===
    $stmt = db()->prepare("
        INSERT INTO system_settings (setting_name, value, enabled)
        VALUES (:name, :value, 1)
        ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP
    ");

    // === GUARDAR TRADUCCIONES ===
    if (!empty($_POST['translations']) && is_array($_POST['translations'])) {
        if (function_exists('save_translation')) {
            foreach ($_POST['translations'] as $transKey => $langValues) {
                if (is_array($langValues)) {
                    foreach ($langValues as $lang => $value) {
                        save_translation($lang, $transKey, $value);
                    }
                }
            }
        }
        // Limpiar cache de traducciones después de guardar
        if (function_exists('clear_translations_cache')) {
            clear_translations_cache();
        }
    }

    foreach ($_POST as $key => $value) {
        if ($key === 'translations') continue;
        if (!is_array($value)) {
            $stmt->execute([':name' => $key, ':value' => $value]);
        }
    }

    log_system_action('update_config', 'Actualizó configuraciones del sistema', 'system_settings');

    echo json_encode(['success' => true, 'message' => 'Configuraciones guardadas correctamente.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}