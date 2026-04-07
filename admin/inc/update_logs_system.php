<?php
/**
 * Auto-update script para bases de datos existentes
 * Agrega el permiso de Ver Logs y crea la tabla system_logs si no existe
 */

require_once __DIR__ . '/../inc/config.php';

if (!db()) {
    die('Error: No se puede conectar a la base de datos');
}

$results = [];

try {
    // 1. Verificar si el permiso ya existe
    $stmt = db()->prepare("SELECT id FROM permissions WHERE id = 23");
    $stmt->execute();
    $permisoExiste = $stmt->fetch();

    if (!$permisoExiste) {
        // Insertar el permiso
        $stmt = db()->prepare("
            INSERT INTO permissions (id, name, category, created_at, updated_at)
            VALUES (23, 'Ver Logs', 'Sistema', NOW(), NOW())
        ");
        $stmt->execute();
        $results[] = "Permiso 'Ver Logs' agregado correctamente";
    } else {
        $results[] = "El permiso 'Ver Logs' ya existe";
    }

    // 2. Verificar si la tabla existe
    $stmt = db()->query("SHOW TABLES LIKE 'system_logs'");
    $tablaExiste = $stmt->fetch();

    if (!$tablaExiste) {
        // Crear la tabla
        db()->exec("
            CREATE TABLE `system_logs` (
              `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_id` int DEFAULT NULL,
              `username` varchar(50) DEFAULT NULL,
              `action` varchar(100) NOT NULL,
              `description` text,
              `entity_type` varchar(50) DEFAULT NULL,
              `entity_id` bigint DEFAULT NULL,
              `ip_address` varchar(45) DEFAULT NULL,
              `user_agent` text,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_user_id` (`user_id`),
              KEY `idx_action` (`action`),
              KEY `idx_entity` (`entity_type`, `entity_id`),
              KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "Tabla 'system_logs' creada correctamente";
    } else {
        $results[] = "La tabla 'system_logs' ya existe";
    }

    echo json_encode(['success' => true, 'message' => implode("\n", $results)]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
