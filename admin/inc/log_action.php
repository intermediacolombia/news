<?php
/**
 * Registra una acción en la tabla system_logs.
 * Usar en cualquier parte del admin después de una acción importante.
 *
 * Uso:
 *   require_once __DIR__ . '/../inc/log_action.php';
 *   log_action('Crear Post', json_encode(['titulo' => $titulo]), 'blog_posts', $postId);
 */
function log_action(string $action, string $description = '', string $entityType = '', $entityId = null): void {
    try {
        $userId    = $_SESSION['user']['id']       ?? null;
        $username  = $_SESSION['user']['username'] ?? 'sistema';
        $ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = db()->prepare("
            INSERT INTO system_logs
                (user_id, username, action, description, entity_type, entity_id, ip_address, user_agent, created_at)
            VALUES
                (:user_id, :username, :action, :description, :entity_type, :entity_id, :ip_address, :user_agent, NOW())
        ");
        $stmt->execute([
            ':user_id'     => $userId,
            ':username'    => $username,
            ':action'      => $action,
            ':description' => $description,
            ':entity_type' => $entityType,
            ':entity_id'   => $entityId,
            ':ip_address'  => $ip,
            ':user_agent'  => $userAgent,
        ]);
    } catch (Throwable $e) {
        // Los logs nunca deben romper el flujo principal
    }
}
