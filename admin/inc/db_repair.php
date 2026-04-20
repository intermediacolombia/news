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
        'translations'=> [],
        'errors'      => [],
    ];

    try {
        // =====================================================================
        // SECCIÓN 0: TRADUCCIONES NUEVAS
        // Agregar aquí cada nueva traducción necesaria para el sistema.
        // Formato: [lang_code, trans_key, trans_value]
        // =====================================================================
        $translations = [
            // Author page translations
            ['es', 'theme_articulo_publicado', 'artículo publicado'],
            ['es', 'theme_articulos_publicados', 'artículos publicados'],
            ['en', 'theme_articulo_publicado', 'published article'],
            ['en', 'theme_articulos_publicados', 'published articles'],
            // Comments system translations
            ['es', 'theme_comentarios', 'Comentarios'],
            ['es', 'theme_dejar_comentario', 'Dejar un comentario'],
            ['es', 'theme_nombre', 'Nombre'],
            ['es', 'theme_email', 'Correo electrónico'],
            ['es', 'theme_comentario', 'Comentario'],
            ['es', 'theme_enviar_comentario', 'Enviar comentario'],
            ['es', 'theme_comentario_enviado', 'Tu comentario ha sido enviado y está pendiente de aprobación'],
            ['es', 'theme_comentario_error', 'Error al enviar el comentario'],
            ['es', 'theme_sin_comentarios', 'Aún no hay comentarios. ¡Sé el primero en comentar!'],
            ['es', 'theme_comentarios_post', 'comentarios'],
            ['es', 'theme_responder', 'Responder'],
            ['en', 'theme_comentarios', 'Comments'],
            ['en', 'theme_dejar_comentario', 'Leave a comment'],
            ['en', 'theme_nombre', 'Name'],
            ['en', 'theme_email', 'Email'],
            ['en', 'theme_comentario', 'Comment'],
            ['en', 'theme_enviar_comentario', 'Submit comment'],
            ['en', 'theme_comentario_enviado', 'Your comment has been sent and is pending approval'],
            ['en', 'theme_comentario_error', 'Error sending comment'],
            ['en', 'theme_sin_comentarios', 'No comments yet. Be the first to comment!'],
            ['en', 'theme_comentarios_post', 'comments'],
            ['en', 'theme_responder', 'Reply'],
            // Menu translations
            ['es', 'menu_comentarios', 'Comentarios'],
            ['en', 'menu_comentarios', 'Comments'],
        ];

        foreach ($translations as [$langCode, $transKey, $transValue]) {
            try {
                $stmt = db()->prepare("
                    SELECT COUNT(*) FROM system_translations
                    WHERE lang_code = ? AND trans_key = ?
                ");
                $stmt->execute([$langCode, $transKey]);
                if ($stmt->fetchColumn() == 0) {
                    db()->prepare("
                        INSERT INTO system_translations (lang_code, trans_key, trans_value, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())
                    ")->execute([$langCode, $transKey, $transValue]);
                    $results['translations'][] = "Agregada traducción: $transKey ($langCode)";
                } else {
                    $results['translations'][] = "Ya existe traducción: $transKey ($langCode)";
                }
            } catch (Exception $e) {
                $results['errors'][] = "Error traducción $transKey ($langCode): " . $e->getMessage();
            }
        }

        // =====================================================================
        // SECCIÓN 1: PERMISOS
        // Formato: [id, 'Nombre permiso', 'Categoría']
        // Agregar aquí cada nuevo permiso necesario.
        // =====================================================================
        $newPermissions = [
            [23, 'Ver Logs',         'Sistema'],
            [24, 'Actualizar Sistema', 'Sistema'],
            [25, 'Gestionar Comentarios', 'Contenido'],
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
            'comments' => "
                CREATE TABLE IF NOT EXISTS `comments` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `post_id` int NOT NULL,
                  `user_id` int DEFAULT NULL,
                  `nombre` varchar(100) NOT NULL,
                  `email` varchar(150) NOT NULL,
                  `contenido` text NOT NULL,
                  `estado` enum('pending','approved','hidden') NOT NULL DEFAULT 'pending',
                  `ip_address` varchar(45) DEFAULT NULL,
                  `user_agent` text,
                  `borrado` tinyint(1) NOT NULL DEFAULT '0',
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_post_id` (`post_id`),
                  KEY `idx_user_id` (`user_id`),
                  KEY `idx_estado` (`estado`),
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
            ['popups', 'show_title', "TINYINT(1) DEFAULT 1 AFTER overlay_enabled"],
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
        // Verificar existencia en information_schema antes de crear
        // (MySQL no soporta CREATE INDEX IF NOT EXISTS directamente)
        // =====================================================================
        $indexes = [
            // [tabla, nombre_indice, columnas]
            ['blog_post_views', 'idx_bpv_post_id', '(post_id)'],
            ['blog_post_views', 'idx_bpv_created_at', '(created_at)'],
            ['blog_post_category', 'idx_bpc_category_post', '(category_id, post_id)'],
            ['blog_posts', 'idx_bp_status_deleted_created', '(status, deleted, created_at)'],
            ['blog_categories', 'idx_bc_status_deleted', '(status, deleted)'],
            ['blog_posts', 'idx_bp_slug', '(slug)'],
            ['blog_posts', 'idx_bp_status_created', '(status, deleted, created_at DESC)'],
        ];

        foreach ($indexes as [$table, $indexName, $columns]) {
            try {
                $check = db()->prepare("
                    SELECT COUNT(*) FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                      AND table_name = ?
                      AND index_name = ?
                ");
                $check->execute([$table, $indexName]);
                if ($check->fetchColumn() == 0) {
                    db()->exec("CREATE INDEX `$indexName` ON `$table` $columns");
                    $results['indexes'][] = "Creado: $table.$indexName";
                } else {
                    $results['indexes'][] = "Ya existe: $table.$indexName";
                }
            } catch (Exception $e) {
                $results['errors'][] = "Error índice $table.$indexName: " . $e->getMessage();
            }
        }

    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }

    return $results;
}
