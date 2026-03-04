<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => "Error PHP: $errstr en línea $errline"]);
    exit;
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Fatal: ' . $error['message'] . ' línea ' . $error['line']]);
    }
});

session_start();
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

if (empty($_FILES['file']['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibió archivo']); exit;
}

$file    = $_FILES['file'];
$alt     = trim($_POST['alt']     ?? '');
$caption = trim($_POST['caption'] ?? '');

function getMimeType($filePath, $fileName) {
    // Método 1: finfo (más seguro)
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        return $mime;
    }

    // Método 2: getimagesize para imágenes
    $info = @getimagesize($filePath);
    if (!empty($info['mime'])) return $info['mime'];

    // Método 3: por extensión como fallback
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $map = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
        'pdf'  => 'application/pdf',
    ];
    return $map[$ext] ?? 'application/octet-stream';
}

$mime = getMimeType($file['tmp_name'], $file['name']);
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowedExts = ['jpg','jpeg','png','webp','gif','svg','ico'];

if (!in_array($ext, $allowedExts)) {
    echo json_encode(['success' => false, 'message' => 'Tipo no permitido. Solo JPG, PNG, WebP, GIF, SVG, ICO.']); exit;
}
if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'El archivo supera los 20MB.']); exit;
}

// Verificar que el directorio base existe
$baseDir = realpath(__DIR__ . '/../../public');
if (!$baseDir) {
    echo json_encode(['success' => false, 'message' => 'Directorio public no encontrado: ' . __DIR__ . '/../../public']); exit;
}

$subDir    = '/public/uploads/image/' . date('Y/m') . '/';
$uploadDir = __DIR__ . '/../../' . $subDir;

// Crear directorio con manejo de error
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio: ' . $uploadDir]); exit;
    }
}

// Verificar que es escribible
if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Directorio sin permisos de escritura: ' . $uploadDir]); exit;
}

$fileName = time() . '_' . preg_replace('/[^a-z0-9\.\-]/i', '_', $file['name']);
$filePath = $subDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
    echo json_encode(['success' => false, 'message' => 'move_uploaded_file falló. tmp: ' . $file['tmp_name'] . ' dest: ' . $uploadDir . $fileName]); exit;
}

$info   = @getimagesize($uploadDir . $fileName);
$width  = $info[0] ?? null;
$height = $info[1] ?? null;

try {
    db()->prepare("INSERT INTO multimedia 
        (file_name, file_path, file_type, mime_type, file_size, width, height, alt_text, caption, uploaded_by, origin)
        VALUES (?,?,'image',?,?,?,?,?,?,?,'blog')")
        ->execute([
            $fileName,
            $filePath,
            $mime,
            $file['size'],
            $width,
            $height,
            $alt,
            $caption,
            $_SESSION['user']['id'],
        ]);
} catch (Throwable $e) {
    // Archivo subido pero error en BD — no bloquear
    // Descomentar para debug:
    // echo json_encode(['success' => false, 'message' => 'BD: ' . $e->getMessage()]); exit;
}

echo json_encode([
    'success' => true,
    'path'    => $filePath,
    'url'     => URLBASE . '/' . $filePath,
    'name'    => $fileName,
    'alt'     => $alt,
    'caption' => $caption,
]);