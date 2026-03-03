<?php
session_start();
require_once __DIR__ . '/../../inc/config.php';
header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$q       = trim($_GET['q']    ?? '');
$type    = trim($_GET['type'] ?? 'image');
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 24;
$offset  = ($page - 1) * $perPage;

$where  = "WHERE deleted = 0";
$params = [];

if ($type) { $where .= " AND file_type = ?"; $params[] = $type; }
if ($q)    { $where .= " AND (file_name LIKE ? OR alt_text LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }

$total = db()->prepare("SELECT COUNT(*) FROM multimedia $where");
$total->execute($params);
$totalFiles = (int)$total->fetchColumn();
$totalPages = (int)ceil($totalFiles / $perPage);

$stmt = db()->prepare("SELECT file_name, file_path, alt_text, caption, file_size, width, height 
                       FROM multimedia $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$files = array_map(fn($r) => [
    'name'    => $r['file_name'],
    'path'    => $r['file_path'],
    'url'     => URLBASE . '/' . $r['file_path'],
    'alt'     => $r['alt_text'] ?? '',
    'caption' => $r['caption']  ?? '',
    'size'    => $r['file_size'] ? round($r['file_size']/1024) . 'KB' : '',
    'dims'    => ($r['width'] && $r['height']) ? $r['width'].'×'.$r['height'].'px' : '',
], $rows);

echo json_encode([
    'success'     => true,
    'files'       => $files,
    'total'       => $totalFiles,
    'page'        => $page,
    'total_pages' => $totalPages,
], JSON_UNESCAPED_UNICODE);