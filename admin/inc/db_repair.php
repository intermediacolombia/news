<?php
/**
 * Función de reparación/migración de base de datos.
 *
 * Se llama en DOS lugares:
 *  1. admin/config/repair_db.php  → manualmente desde el panel de admin
 *  2. admin/inc/auto-update.php   → automáticamente tras cada git pull
 *
 * =====================================================================
 * INSTRUCCIONES PARA DESARROLLADORES / IA:
 * Cada vez que una nueva funcionalidad requiera cambios en la BD
 * (nueva tabla, nueva columna, nuevo permiso), DEBES agregarlo aquí
 * en la sección correspondiente. Esto garantiza que todos los sitios
 * que corren este sistema actualicen su BD automáticamente al hacer
 * git pull, sin intervención manual.
 * =====================================================================
 */
function repair_database(): array {
    $results = [
        'permissions' => [],
        'tables'      => [],
        'columns'     => [],
        'indexes'     => [],
        'errors'      => [],
    ];

    try {
        // =====================================================================
        // SECCIÓN 1: PERMISOS
        // Formato: [id, 'Nombre permiso', 'Categoría']
        // Agregar aquí cada nuevo permiso necesario.
        // =====================================================================
        $newPermissions = [
            [23, 'Ver Logs',         'Sistema'],
            [24, 'Actualizar Sistema', 'Sistema'],
        ];

        foreach ($newPermissions as [$id, $name, $category]) {
            $stmt = db()->prepare("SELECT id FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                db()->prepare("INSERT INTO permissions (id, name, category, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())")
                   ->execute([$id, $name, $category]);
                $results['permissions'][] = "Agregado: $name";
            } else {
                $results['permissions'][] = "Ya existe: $name";
            }
        }

        // =====================================================================
        // SECCIÓN 2: TABLAS NUEVAS
        // Usar CREATE TABLE IF NOT EXISTS — nunca afecta datos existentes.
        // Agregar aquí cada nueva tabla que requiera una funcionalidad nueva.
        // =====================================================================
        $tables = [
            'system_logs' => "
                CREATE TABLE IF NOT EXISTS `system_logs` (
                  `id`          bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                  `user_id`     int DEFAULT NULL,
                  `username`    varchar(50) DEFAULT NULL,
                  `action`      varchar(100) NOT NULL,
                  `description` text,
                  `entity_type` varchar(50) DEFAULT NULL,
                  `entity_id`   bigint DEFAULT NULL,
                  `ip_address`  varchar(45) DEFAULT NULL,
                  `user_agent`  text,
                  `created_at`  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_user_id`    (`user_id`),
                  KEY `idx_action`     (`action`),
                  KEY `idx_entity`     (`entity_type`, `entity_id`),
                  KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
        ];

        foreach ($tables as $tableName => $sql) {
            $stmt = db()->query("SHOW TABLES LIKE '$tableName'");
            if (!$stmt->fetch()) {
                db()->exec($sql);
                $results['tables'][] = "Creada tabla: $tableName";
            } else {
                $results['tables'][] = "Ya existe tabla: $tableName";
            }
        }

        // =====================================================================
        // SECCIÓN 3: COLUMNAS NUEVAS EN TABLAS EXISTENTES
        // Formato: ['tabla', 'columna', 'DEFINICIÓN SQL (tipo + posición)']
        // Agregar aquí cada nuevo campo en una tabla existente.
        // Ejemplo: ['usuarios', 'avatar', "VARCHAR(255) DEFAULT NULL AFTER email"]
        // =====================================================================
        $columns = [
            // Agregar aquí cuando sea necesario
        ];

        foreach ($columns as [$table, $column, $definition]) {
            $stmt = db()->prepare("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = ?
                  AND COLUMN_NAME  = ?
            ");
            $stmt->execute([$table, $column]);
            if ($stmt->fetchColumn() == 0) {
                db()->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
                $results['columns'][] = "Agregada columna: $table.$column";
            } else {
                $results['columns'][] = "Ya existe columna: $table.$column";
            }
        }

        // =====================================================================
        // SECCIÓN 4: ÍNDICES DE RENDIMIENTO
        // CREATE INDEX ... si no existen — mejora consultas frecuentes.
        // =====================================================================
        $indexes = [
            // blog_post_views: acelerar conteo de vistas por post
            "CREATE INDEX IF NOT EXISTS idx_bpv_post_id ON blog_post_views (post_id)",
            // blog_post_views: acelerar consultas por fecha
            "CREATE INDEX IF NOT EXISTS idx_bpv_created_at ON blog_post_views (created_at)",
            // blog_post_category: acelerar JOIN por categoría
            "CREATE INDEX IF NOT EXISTS idx_bpc_category_post ON blog_post_category (category_id, post_id)",
            // blog_posts: acelerar filtrado por status + deleted + fecha
            "CREATE INDEX IF NOT EXISTS idx_bp_status_deleted_created ON blog_posts (status, deleted, created_at)",
            // blog_categories: acelerar filtrado por status
            "CREATE INDEX IF NOT EXISTS idx_bc_status_deleted ON blog_categories (status, deleted)",
            // blog_posts: acelerar búsqueda por slug
            "CREATE INDEX IF NOT EXISTS idx_bp_slug ON blog_posts (slug)",
            // blog_posts: acelerar consultas de posts populares
            "CREATE INDEX IF NOT EXISTS idx_bp_status_created ON blog_posts (status, deleted, created_at DESC)",
        ];

        foreach ($indexes as $sql) {
            try {
                db()->exec($sql);
                preg_match('/INDEX\s+\S+\s+ON\s+(\S+)/i', $sql, $match);
                $idxName = $match[1] ?? 'unknown';
                $results['indexes'][] = "Índice creado/verificado en: $idxName";
            } catch (Exception $e) {
                $results['errors'][] = "Error índice: " . $e->getMessage();
            }
        }

    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }

    return $results;
}
