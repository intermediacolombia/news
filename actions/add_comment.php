<?php
/**
 * Agregar nuevo comentario desde frontend
 * Anti-spam: honeypot, rate limiting, validación
 */
require_once __DIR__ . '/../inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// ========================================
// ANTI-SPAM: Honeypot field
// ========================================
if (!empty($_POST['website']) || !empty($_POST['phone'])) {
    // Bot detected, silently ignore
    echo json_encode(['success' => true, 'message' => 'Comentario enviado']);
    exit;
}

// ========================================
// ANTI-SPAM: Rate limiting (5 per hour per IP)
// ========================================
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

$stmt = db()->prepare("
    SELECT COUNT(*) FROM comments 
    WHERE ip_address = ? AND created_at > ?
");
$stmt->execute([$ipAddress, $oneHourAgo]);
$recentComments = (int)$stmt->fetchColumn();

if ($recentComments >= 5) {
    echo json_encode([
        'success' => false, 
        'message' => 'Has alcanzado el límite de comentarios. Espera un momento antes de comentar nuevamente.'
    ]);
    exit;
}

// ========================================
// Validate required fields
// ========================================
$postId = (int)($_POST['post_id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');

if (!$postId || !$nombre || !$email || !$contenido) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Validate content length (min 10 chars, max 2000)
if (mb_strlen($contenido) < 10) {
    echo json_encode(['success' => false, 'message' => 'El comentario debe tener al menos 10 caracteres']);
    exit;
}

if (mb_strlen($contenido) > 2000) {
    echo json_encode(['success' => false, 'message' => 'El comentario no puede exceder los 2000 caracteres']);
    exit;
}

// Validate name length (max 100)
if (mb_strlen($nombre) > 100) {
    echo json_encode(['success' => false, 'message' => 'Nombre demasiado largo']);
    exit;
}

// Check if post exists
$stmt = db()->prepare("SELECT id FROM blog_posts WHERE id = ? AND status = 'published' AND deleted = 0");
$stmt->execute([$postId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Artículo no encontrado']);
    exit;
}

// ========================================
// Anti-spam: Check for suspicious patterns
// ========================================
$spamPatterns = [
    '/\[url=.*\]/i',
    '/<a href.*>/i',
    '/https?:\/\/.*\s.*https?:\/\//i',  // Multiple URLs
    '/viagra|cialis|pharmacy|casino|lottery/i'  // Common spam keywords
];

foreach ($spamPatterns as $pattern) {
    if (preg_match($pattern, $contenido)) {
        echo json_encode(['success' => false, 'message' => 'Comentario rechazado por contener contenido sospechoso']);
        exit;
    }
}

// ========================================
// Get user_id if logged in
// ========================================
$userId = null;
if (isset($_SESSION['usuario_id'])) {
    $userId = (int)$_SESSION['usuario_id'];
}

// ========================================
// Insert comment (pending by default)
// ========================================
try {
    $stmt = db()->prepare("
        INSERT INTO comments (post_id, user_id, nombre, email, contenido, estado, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)
    ");
    
    $stmt->execute([
        $postId,
        $userId,
        htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($contenido, ENT_QUOTES, 'UTF-8'),
        $ipAddress,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tu comentario ha sido enviado y está pendiente de aprobación'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al enviar el comentario. Intenta nuevamente.'
    ]);
}
