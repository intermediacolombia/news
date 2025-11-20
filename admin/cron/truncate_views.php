<?php
/**
 * clear_views.php
 * Vacía la tabla blog_post_views
 * Para ejecutarlo manualmente o desde Chrome Scheduler / cron-job.org
 */

session_start();

// Cargar config con ruta absoluta para evitar duplicaciones
require_once realpath(__DIR__ . '/../inc/config.php');

try {
    // Vaciar tabla
    db()->exec("TRUNCATE TABLE blog_post_views");

    // Mostrar respuesta para verificar que funcionó
    echo "OK: Tabla blog_post_views vaciada correctamente. " . date('Y-m-d H:i:s');

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
