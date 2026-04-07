<?php
session_start();
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$root   = realpath(__DIR__ . '/../../') . '/';

$scanDirs = [
    'public/images/blog' => 'blog',
    'public/images/ads'  => 'ads',
    'public/images'      => 'manual',
    'public/uploads'     => 'manual',
    'public/img'         => 'manual',
];

$fileTypes = [
    'jpg'  => 'image',  'jpeg' => 'image',
    'png'  => 'image',  'webp' => 'image',
    'gif'  => 'image',  'mp4'  => 'video',
    'webm' => 'video',  'mp3'  => 'audio',
    'wav'  => 'audio',  'pdf'  => 'document',
];

$mimeTypes = [
    'jpg'  => 'image/jpeg',   'jpeg' => 'image/jpeg',
    'png'  => 'image/png',    'webp' => 'image/webp',
    'gif'  => 'image/gif',    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',   'mp3'  => 'audio/mpeg',
    'wav'  => 'audio/wav',    'pdf'  => 'application/pdf',
];

// Cargar rutas ya registradas para lookup O(1)
$existing = db()->query("SELECT file_path FROM multimedia WHERE deleted = 0")
    ->fetchAll(PDO::FETCH_COLUMN);
$existing = array_flip($existing);

$inserted = 0;
$skipped  = 0;
$errors   = 0;

$stmt = db()->prepare("INSERT IGNORE INTO multimedia 
    (file_name, file_path, file_type, mime_type, file_size, width, height, uploaded_by, origin)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($scanDirs as $relDir => $origin) {
    $absDir = realpath($root . $relDir);
    if (!$absDir || !is_dir($absDir)) continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) continue;

        $ext = strtolower($fileInfo->getExtension());
        if (!isset($fileTypes[$ext])) continue;

        // Ruta relativa desde raíz del proyecto
        $absPath = $fileInfo->getRealPath();
        $relPath = str_replace('\\', '/', ltrim(str_replace($root, '', $absPath), '/'));

        if (isset($existing[$relPath])) {
            $skipped++;
            continue;
        }

        try {
            $fileName = $fileInfo->getFilename();
            $fileSize = $fileInfo->getSize();
            $fileType = $fileTypes[$ext];
            $mime     = $mimeTypes[$ext] ?? 'application/octet-stream';

            $width = $height = null;
            if ($fileType === 'image') {
                $info   = @getimagesize($absPath);
                $width  = $info[0] ?? null;
                $height = $info[1] ?? null;
            }

            $stmt->execute([
                $fileName,
                $relPath,
                $fileType,
                $mime,
                $fileSize,
                $width,
                $height,
                $userId,
                $origin,
            ]);

            $existing[$relPath] = true;
            $inserted++;

        } catch (Throwable $e) {
            $errors++;
        }
    }
}

echo json_encode([
    'success'  => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'errors'   => $errors,
    'message'  => "Escaneo completado: $inserted nuevos, $skipped ya existían" . ($errors ? ", $errors errores" : ''),
], JSON_UNESCAPED_UNICODE);

log_system_action('scan_multimedia', "Escaneó archivos multimedia: $inserted nuevos, $skipped existentes", 'multimedia');