<?php
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

$mime = mime_content_type($file['tmp_name']);
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed = [
    'image/jpeg' => ['jpg','jpeg'],
    'image/png'  => ['png'],
    'image/webp' => ['webp'],
    'image/gif'  => ['gif'],
];

if (!isset($allowed[$mime]) || !in_array($ext, $allowed[$mime])) {
    echo json_encode(['success' => false, 'message' => 'Tipo no permitido. Solo JPG, PNG, WebP, GIF.']); exit;
}
if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'El archivo supera los 20MB.']); exit;
}

$subDir    = 'public/uploads/image/' . date('Y/m') . '/';
$uploadDir = __DIR__ . '/../../' . $subDir;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$fileName = time() . '_' . preg_replace('/[^a-z0-9\.\-]/i', '_', $file['name']);
$filePath = $subDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo.']); exit;
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
    // El archivo ya se subió, no bloquear por error de BD
}

echo json_encode([
    'success' => true,
    'path'    => $filePath,
    'url'     => URLBASE . '/' . $filePath,
    'name'    => $fileName,
    'alt'     => $alt,
    'caption' => $caption,
]);