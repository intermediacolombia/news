<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Multimedia';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

db()->exec("SET NAMES utf8mb4");

/* ── Eliminar individual ── */
if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $file = db()->prepare("SELECT file_path FROM multimedia WHERE id = ? AND deleted = 0 LIMIT 1");
    $file->execute([$id]);
    $row  = $file->fetch();
    if ($row) {
        $abs = __DIR__ . '/../../' . ltrim($row['file_path'], '/');
        if (file_exists($abs)) unlink($abs);
        db()->prepare("UPDATE multimedia SET deleted = 1 WHERE id = ?")->execute([$id]);
        log_system_action('delete_multimedia', 'Eliminó archivo multimedia ID: ' . $id, 'multimedia', $id);
        setFlash('success', 'Archivo eliminado.');
    }
    header("Location: index.php"); exit;
}

/* ── Subir archivo ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file    = $_FILES['file'];
    $altText = trim($_POST['alt_text'] ?? '');
    $caption = trim($_POST['caption']  ?? '');

    $allowed = [
        'image/jpeg'      => ['jpg','jpeg'],
        'image/png'       => ['png'],
        'image/webp'      => ['webp'],
        'image/gif'       => ['gif'],
        'video/mp4'       => ['mp4'],
        'video/webm'      => ['webm'],
        'audio/mpeg'      => ['mp3'],
        'audio/wav'       => ['wav'],
        'application/pdf' => ['pdf'],
    ];

    // getMimeType sin depender de mime_content_type
    function getMimeType($filePath, $fileName) {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mime;
        }
        $info = @getimagesize($filePath);
        if (!empty($info['mime'])) return $info['mime'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $map = [
            'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
            'webp'=>'image/webp','gif'=>'image/gif','mp4'=>'video/mp4',
            'webm'=>'video/webm','mp3'=>'audio/mpeg','wav'=>'audio/wav',
            'pdf'=>'application/pdf',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }

    $mime = getMimeType($file['tmp_name'], $file['name']);
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!isset($allowed[$mime]) || !in_array($ext, $allowed[$mime])) {
        setFlash('error', 'Tipo de archivo no permitido.');
        header("Location: index.php"); exit;
    }
    if ($file['size'] > 20 * 1024 * 1024) {
        setFlash('error', 'El archivo supera los 20MB.');
        header("Location: index.php"); exit;
    }

    $fileType = match(true) {
        str_starts_with($mime, 'image/') => 'image',
        str_starts_with($mime, 'video/') => 'video',
        str_starts_with($mime, 'audio/') => 'audio',
        $mime === 'application/pdf'       => 'document',
        default                           => 'other',
    };

    $subDir    = 'public/uploads/' . $fileType . '/' . date('Y/m') . '/';
    $uploadDir = __DIR__ . '/../../' . $subDir;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileName = time() . '_' . preg_replace('/[^a-z0-9\.\-]/i', '_', $file['name']);
    $filePath = $subDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
        setFlash('error', 'No se pudo guardar el archivo.');
        header("Location: index.php"); exit;
    }

    $width = $height = null;
    if ($fileType === 'image') {
        $info   = @getimagesize($uploadDir . $fileName);
        $width  = $info[0] ?? null;
        $height = $info[1] ?? null;
    }

    db()->prepare("INSERT INTO multimedia
        (file_name, file_path, file_type, mime_type, file_size, width, height, alt_text, caption, uploaded_by, origin)
        VALUES (?,?,?,?,?,?,?,?,?,?,'manual')")
        ->execute([
            $fileName, $filePath, $fileType, $mime,
            $file['size'], $width, $height, $altText, $caption,
            $id_user
        ]);

    setFlash('success', 'Archivo subido correctamente.');
    header("Location: index.php"); exit;
}

/* ── Filtros y paginación ── */
$filterType = $_GET['type'] ?? '';
$filterQ    = trim($_GET['q']  ?? '');
$page       = max(1, (int)($_GET['p']  ?? 1));
$ppRaw      = isset($_GET['pp']) ? (int)$_GET['pp'] : 24;
$perPage    = in_array($ppRaw, [24, 48, 96, 200]) ? $ppRaw : 24;
$offset     = ($page - 1) * $perPage;

$where  = "WHERE deleted = 0";
$params = [];
if ($filterType) { $where .= " AND file_type = ?";                         $params[] = $filterType; }
if ($filterQ)    { $where .= " AND (file_name LIKE ? OR alt_text LIKE ?)"; $params[] = "%$filterQ%"; $params[] = "%$filterQ%"; }

$total      = db()->prepare("SELECT COUNT(*) FROM multimedia $where");
$total->execute($params);
$totalFiles = (int)$total->fetchColumn();
$totalPages = $totalFiles > 0 ? (int)ceil($totalFiles / $perPage) : 1;

$stmt = db()->prepare("SELECT * FROM multimedia $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// QS base para paginación (conserva todos los filtros)
$qsPag = 'type=' . urlencode($filterType)
       . '&q='   . urlencode($filterQ)
       . '&pp='  . $perPage;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Multimedia</title>
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; }

    /* Card base */
    .media-card {
        border:1px solid #dee2e6; border-radius:8px; overflow:hidden;
        background:#fff; position:relative; cursor:pointer;
        transition:.15s; user-select:none;
    }
    .media-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.15); transform:translateY(-2px); }

    /* Seleccionada */
    .media-card.selected { outline:3px solid #0d6efd; box-shadow:0 0 0 1px #0d6efd; }
    .media-card.selected .card-check { display:flex !important; }

    /* Checkbox overlay */
    .card-check {
        display:none; position:absolute; top:6px; left:6px; z-index:3;
        width:24px; height:24px; border-radius:50%;
        background:#0d6efd; color:#fff;
        align-items:center; justify-content:center;
        font-size:14px; box-shadow:0 2px 4px rgba(0,0,0,.3);
    }

    .media-thumb { width:100%; height:140px; background:#f8f9fa; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .media-thumb img { width:100%; height:140px; object-fit:cover; }
    .media-thumb .media-icon { font-size:48px; color:#6c757d; }
    .media-info { padding:8px; font-size:12px; }
    .media-info .name { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .media-actions { display:flex; gap:4px; padding:0 8px 8px; }

    .upload-zone { border:2px dashed #dee2e6; border-radius:8px; padding:24px 12px; text-align:center; cursor:pointer; transition:.2s; }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#0d6efd; background:#f0f4ff; }

    /* Miniaturas de la cola de subida */
    .uq-thumb { position:relative; border-radius:5px; overflow:hidden; border:1px solid #dee2e6; background:#f8f9fa; }
    .uq-thumb img { width:100%; height:56px; object-fit:cover; display:block; }
    .uq-thumb .uq-icon { height:56px; display:flex; align-items:center; justify-content:center; font-size:22px; color:#6c757d; }
    .uq-thumb .uq-name { font-size:9px; padding:1px 3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; background:#fff; }
    .uq-thumb .uq-rm { position:absolute; top:2px; right:2px; width:16px; height:16px; border-radius:50%;
                       background:rgba(220,53,69,.85); border:none; color:#fff; font-size:10px; line-height:1;
                       cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0; }
    .uq-thumb .uq-status { position:absolute; inset:0; background:rgba(255,255,255,.75);
                           display:flex; align-items:center; justify-content:center; font-size:18px; }

    .type-badge-image    { background:#d1fae5; color:#065f46; }
    .type-badge-video    { background:#dbeafe; color:#1e40af; }
    .type-badge-audio    { background:#fce7f3; color:#9d174d; }
    .type-badge-document { background:#fef3c7; color:#92400e; }
    .type-badge-other    { background:#f3f4f6; color:#374151; }
    .btn-xs { padding:2px 8px; font-size:12px; }
    .page-header {
      background: #fff;
      border-radius: 12px;
      padding: 1.2rem 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: .5rem;
    }
    .page-header h4 {
      margin: 0;
      font-weight: 700;
      color: #1e293b;
    }
    @keyframes fadeIn { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }

    /* Barra de acciones en lote */
    #batch-bar {
        background: linear-gradient(135deg,#214A82,#4972AA);
        color:#fff; border-radius:8px; padding:10px 16px;
        margin-bottom:12px; display:none;
        align-items:center; gap:12px; flex-wrap:wrap;
    }
    #batch-bar.visible { display:flex; }
    #batch-bar .badge-count {
        background:rgba(255,255,255,.25); color:#fff;
        border-radius:20px; padding:2px 12px; font-weight:600;
    }

    /* Hint Ctrl */
    .ctrl-hint {
        font-size:12px; color:#6c757d;
        display:flex; align-items:center; gap:4px;
    }
    .ctrl-hint kbd {
        background:#e9ecef; border:1px solid #dee2e6;
        border-radius:3px; padding:1px 5px; font-size:11px;
    }
  </style>
</head>
<body>

<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container-fluid py-4">
  <?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

  <!-- Page header -->
  <div class="page-header">
    <h4><i class="bi bi-images me-2" style="color:var(--primary-color)"></i>Multimedia</h4>
  </div>

  <div class="row g-4">

    <!-- ══ Columna izquierda ══ -->
    <div class="col-lg-3">

      <!-- Subir -->
      <div class="card mb-3">
        <div class="card-header bg-light"><strong><i class="bi bi-cloud-upload me-2"></i>Subir archivos</strong></div>
        <div class="card-body p-2">

          <!-- Zona drop -->
          <div class="upload-zone mb-2" id="upload-zone">
            <i class="bi bi-cloud-arrow-up fs-2 text-muted"></i>
            <div class="mt-1 text-muted small">Arrastra o haz clic para subir varios</div>
            <div class="text-muted" style="font-size:10px;">JPG, PNG, WebP, GIF, MP4, MP3, PDF — Máx 20 MB</div>
          </div>
          <input type="file" id="file-input" multiple
                 accept="image/*,video/mp4,video/webm,audio/mpeg,audio/wav,application/pdf"
                 class="d-none">

          <!-- Cola de archivos seleccionados -->
          <div id="upload-queue" class="d-none">
            <div id="upload-queue-grid" class="row g-1 mb-2"></div>
            <button type="button" class="btn btn-primary btn-sm w-100" id="btn-upload" disabled>
              <i class="bi bi-upload me-1"></i>
              Subir <span id="upload-file-count">0</span> archivo(s)
            </button>
            <!-- Progreso -->
            <div id="upload-progress-wrap" class="d-none mt-2">
              <div class="progress mb-1" style="height:8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     id="upload-progress-bar" style="width:0%;transition:width .3s;"></div>
              </div>
              <div class="text-muted text-center" style="font-size:11px;" id="upload-progress-text">
                Preparando...
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Stats -->
      <div class="card mb-3">
        <div class="card-header bg-light"><strong>Resumen</strong></div>
        <div class="card-body p-0">
          <?php
          $stats = db()->query(
              "SELECT file_type, COUNT(*) as total, SUM(file_size) as total_size
               FROM multimedia WHERE deleted = 0 GROUP BY file_type"
          )->fetchAll(PDO::FETCH_ASSOC);
          $icons = ['image'=>'bi-image','video'=>'bi-camera-video','audio'=>'bi-music-note','document'=>'bi-file-pdf','other'=>'bi-file'];
          ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($stats as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
              <span><i class="bi <?= $icons[$s['file_type']] ?? 'bi-file' ?> me-2"></i><?= ucfirst($s['file_type']) ?></span>
              <span>
                <span class="badge bg-secondary"><?= $s['total'] ?></span>
                <small class="text-muted ms-1"><?= round($s['total_size'] / 1024 / 1024, 1) ?>MB</small>
              </span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!-- Escanear -->
      <div class="card">
        <div class="card-header bg-light"><strong><i class="bi bi-radar me-2"></i>Sincronizar biblioteca</strong></div>
        <div class="card-body">
          <p class="text-muted small mb-3">
            Escanea todas las carpetas del sistema y registra los archivos que aún no están en la biblioteca.
          </p>
          <button type="button" class="btn btn-outline-primary w-100" id="btn-scan-files">
            <i class="bi bi-search me-1"></i> Escanear archivos
          </button>
          <div id="scan-progress" class="mt-3 d-none">
            <div class="progress mb-2">
              <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div>
            </div>
            <div class="text-muted small text-center" id="scan-status">Escaneando...</div>
          </div>
          <div id="scan-result-info" class="mt-3 d-none"></div>
        </div>
      </div>

    </div><!-- /col-lg-3 -->

    <!-- ══ Galería ══ -->
    <div class="col-lg-9">

      <!-- Filtros + por página -->
      <form method="get" class="d-flex gap-2 mb-3 flex-wrap align-items-center" id="filter-form">
        <input type="hidden" name="pp" value="<?= $perPage ?>">
        <input type="text" name="q" class="form-control form-control-sm" style="max-width:180px"
               placeholder="Buscar..." value="<?= htmlspecialchars($filterQ) ?>">
        <select name="type" class="form-select form-select-sm" style="max-width:130px" onchange="this.form.submit()">
          <option value="">Todos los tipos</option>
          <?php foreach (['image'=>'Imágenes','video'=>'Videos','audio'=>'Audio','document'=>'Documentos','other'=>'Otros'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= $filterType === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        <?php if ($filterType || $filterQ): ?>
        <a href="index.php?pp=<?= $perPage ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i> Limpiar</a>
        <?php endif; ?>

        <!-- Selector de cantidad por página -->
        <div class="d-flex align-items-center gap-1 ms-auto">
          <label class="form-label mb-0 small text-muted">Mostrar:</label>
          <select id="per-page-select" class="form-select form-select-sm" style="width:75px">
            <?php foreach ([24,48,96,200] as $pp): ?>
            <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>

      <!-- Barra de acciones en lote -->
      <div id="batch-bar">
        <i class="bi bi-check2-square fs-5"></i>
        <span class="badge-count" id="batch-count">0 seleccionadas</span>
        <button type="button" class="btn btn-sm btn-danger" id="btn-batch-delete">
          <i class="bi bi-trash me-1"></i> Eliminar seleccionadas
        </button>
        <button type="button" class="btn btn-sm btn-light btn-sm" id="btn-batch-clear">
          <i class="bi bi-x me-1"></i> Limpiar selección
        </button>
        <span class="ms-auto ctrl-hint" style="color:rgba(255,255,255,.7)">
          <kbd style="background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.3);color:#fff;">Ctrl</kbd>
          + clic para seleccionar
        </span>
      </div>

      <!-- Info + hint -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small">
          <?= $totalFiles ?> archivos · página <?= $page ?> de <?= max(1,$totalPages) ?>
        </span>
        <span class="ctrl-hint">
          <i class="bi bi-info-circle"></i>
          Mantén <kbd>Ctrl</kbd> y haz clic para seleccionar varios
        </span>
      </div>

      <?php if (empty($files)): ?>
      <div class="text-center text-muted py-5" id="empty-state">
        <i class="bi bi-images fs-1"></i>
        <div class="mt-2">No hay archivos aún</div>
      </div>
      <?php else: ?>

      <div class="media-grid" id="media-grid">
        <?php foreach ($files as $f):
          $isImage   = $f['file_type'] === 'image';
          $fileUrl   = URLBASE . '/' . $f['file_path'];
          $sizeKb    = round($f['file_size'] / 1024);
          $sizeTxt   = $sizeKb > 1024 ? round($sizeKb/1024,1).'MB' : $sizeKb.'KB';
          $typeIcons = ['image'=>'bi-image','video'=>'bi-camera-video','audio'=>'bi-music-note-beamed','document'=>'bi-file-pdf','other'=>'bi-file-earmark'];
        ?>
        <div class="media-card" data-id="<?= $f['id'] ?>">

          <!-- Checkmark de selección -->
          <div class="card-check"><i class="bi bi-check-lg"></i></div>

          <div class="media-thumb">
            <?php if ($isImage): ?>
              <img src="<?= htmlspecialchars($fileUrl) ?>"
                   alt="<?= htmlspecialchars($f['alt_text'] ?? $f['file_name']) ?>"
                   loading="lazy">
            <?php else: ?>
              <div class="d-flex flex-column align-items-center">
                <i class="bi <?= $typeIcons[$f['file_type']] ?? 'bi-file' ?> media-icon"></i>
                <span class="small text-muted mt-1"><?= strtoupper(pathinfo($f['file_name'], PATHINFO_EXTENSION)) ?></span>
              </div>
            <?php endif; ?>
          </div>

          <div class="media-info">
            <div class="name" title="<?= htmlspecialchars($f['file_name']) ?>"><?= htmlspecialchars($f['file_name']) ?></div>
            <div class="text-muted">
              <?= $sizeTxt ?>
              <?php if ($f['width']): ?> · <?= $f['width'] ?>×<?= $f['height'] ?>px<?php endif; ?>
            </div>
            <div>
              <span class="badge type-badge-<?= $f['file_type'] ?>"><?= ucfirst($f['file_type']) ?></span>
              <?php if ($f['origin'] !== 'manual'): ?>
              <span class="badge bg-light text-muted border"><?= htmlspecialchars($f['origin']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="media-actions">
            <button type="button" class="btn btn-xs btn-outline-secondary btn-copy-url flex-fill"
                    data-url="<?= htmlspecialchars($fileUrl) ?>" title="Copiar URL">
              <i class="bi bi-clipboard"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-primary btn-preview-media"
                    data-url="<?= htmlspecialchars($fileUrl) ?>"
                    data-type="<?= $f['file_type'] ?>"
                    data-name="<?= htmlspecialchars($f['file_name']) ?>"
                    data-size="<?= $sizeTxt ?>"
                    data-dims="<?= $f['width'] ? $f['width'].'×'.$f['height'].'px' : '' ?>"
                    title="Ver">
              <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-info btn-edit-media"
                    data-id="<?= $f['id'] ?>"
                    data-type="<?= $f['file_type'] ?>"
                    data-name="<?= htmlspecialchars($f['file_name']) ?>"
                    data-alt="<?= htmlspecialchars($f['alt_text'] ?? '') ?>"
                    data-caption="<?= htmlspecialchars($f['caption'] ?? '') ?>"
                    data-url="<?= htmlspecialchars($fileUrl) ?>"
                    title="Editar">
              <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-danger btn-delete-single"
                    data-id="<?= $f['id'] ?>"
                    title="Eliminar">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Paginación -->
      <?php if ($totalPages > 1):
        $range = 2;
        $pStart = max(1, $page - $range);
        $pEnd   = min($totalPages, $page + $range);
      ?>
      <nav class="mt-4">
        <ul class="pagination pagination-sm justify-content-center flex-wrap">
          <li class="page-item <?= $page<=1?'disabled':'' ?>">
            <a class="page-link" href="?p=1&<?= $qsPag ?>">«</a>
          </li>
          <li class="page-item <?= $page<=1?'disabled':'' ?>">
            <a class="page-link" href="?p=<?= max(1,$page-1) ?>&<?= $qsPag ?>">‹ Anterior</a>
          </li>
          <?php if ($pStart > 1): ?>
          <li class="page-item disabled"><span class="page-link">…</span></li>
          <?php endif; ?>
          <?php for ($i = $pStart; $i <= $pEnd; $i++): ?>
          <li class="page-item <?= $i===$page?'active':'' ?>">
            <a class="page-link" href="?p=<?= $i ?>&<?= $qsPag ?>"><?= $i ?></a>
          </li>
          <?php endfor; ?>
          <?php if ($pEnd < $totalPages): ?>
          <li class="page-item disabled"><span class="page-link">…</span></li>
          <?php endif; ?>
          <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
            <a class="page-link" href="?p=<?= min($totalPages,$page+1) ?>&<?= $qsPag ?>">Siguiente ›</a>
          </li>
          <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
            <a class="page-link" href="?p=<?= $totalPages ?>&<?= $qsPag ?>">»</a>
          </li>
        </ul>
        <div class="text-center text-muted small mt-1">
          Página <?= $page ?> de <?= $totalPages ?> · <?= $totalFiles ?> archivos
        </div>
      </nav>
      <?php endif; ?>

      <?php endif; ?>
    </div><!-- /col-lg-9 -->
  </div><!-- /row -->
</div>

<!-- Modal Preview -->
<div class="modal fade" id="modalPreviewMedia" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title text-truncate" id="preview-modal-name"></h6>
        <div class="d-flex align-items-center gap-2 ms-auto">
          <a id="preview-modal-link" href="#" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-arrow-up-right"></i> Abrir
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body text-center p-2" id="preview-modal-body"></div>
      <div class="modal-footer py-2 justify-content-start">
        <small class="text-muted" id="preview-modal-meta"></small>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditMedia" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar metadata</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="edit-preview-wrap" class="mb-3 text-center d-none">
          <img id="edit-preview-img" src="" alt="" class="img-thumbnail" style="max-height:80px;">
        </div>
        <div id="edit-preview-icon" class="mb-3 text-center d-none">
          <i id="edit-preview-icon-el" class="bi fs-1 text-muted"></i>
          <div class="small text-muted mt-1" id="edit-preview-icon-name"></div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">URL del archivo</label>
          <div class="input-group">
            <input type="text" id="modal-url" class="form-control form-control-sm font-monospace" readonly>
            <button class="btn btn-outline-secondary btn-sm btn-copy-url-modal" type="button">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
        </div>
        <input type="hidden" id="modal-id">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Texto alternativo (alt)</label>
          <input type="text" id="modal-alt" class="form-control form-control-sm">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Caption</label>
          <textarea id="modal-caption" class="form-control form-control-sm" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-save-media">
          <i class="bi bi-check-circle me-1"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Selector cantidad por página ── */
    document.getElementById('per-page-select')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('pp', this.value);
        url.searchParams.set('p', '1');
        window.location.href = url.toString();
    });

    /* ── Upload multi-archivo vía AJAX ── */
    const UPLOAD_URL = '<?= URLBASE ?>/admin/multimedia/upload_ajax.php';
    const zone       = document.getElementById('upload-zone');
    const fileInput  = document.getElementById('file-input');
    const queueWrap  = document.getElementById('upload-queue');
    const queueGrid  = document.getElementById('upload-queue-grid');
    const btnUp      = document.getElementById('btn-upload');
    const countEl    = document.getElementById('upload-file-count');
    const progWrap   = document.getElementById('upload-progress-wrap');
    const progBar    = document.getElementById('upload-progress-bar');
    const progText   = document.getElementById('upload-progress-text');

    let uploadQueue = []; // [{file, objectUrl}]

    /* — Drop zone events — */
    zone.addEventListener('click', () => fileInput.click());
    ['dragenter','dragover'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('drag-over'); }));
    ['dragleave','drop'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.remove('drag-over'); }));
    zone.addEventListener('drop', ev => { addToQueue(ev.dataTransfer.files); });
    fileInput.addEventListener('change', function () { addToQueue(this.files); this.value = ''; });

    function addToQueue(fileList) {
        Array.from(fileList).forEach(f => {
            uploadQueue.push({ file: f, objectUrl: f.type.startsWith('image/') ? URL.createObjectURL(f) : null });
        });
        renderQueue();
    }

    function renderQueue() {
        queueGrid.innerHTML = '';
        uploadQueue.forEach((item, i) => {
            const col = document.createElement('div');
            col.className = 'col-4';
            col.innerHTML = `<div class="uq-thumb">
                ${item.objectUrl
                    ? `<img src="${item.objectUrl}" alt="">`
                    : `<div class="uq-icon"><i class="bi bi-file-earmark"></i></div>`}
                <div class="uq-name" title="${escHtml(item.file.name)}">${escHtml(item.file.name)}</div>
                <button class="uq-rm" data-idx="${i}" title="Quitar">&times;</button>
            </div>`;
            col.querySelector('.uq-rm').addEventListener('click', function () {
                const idx = parseInt(this.dataset.idx);
                if (uploadQueue[idx].objectUrl) URL.revokeObjectURL(uploadQueue[idx].objectUrl);
                uploadQueue.splice(idx, 1);
                renderQueue();
            });
            queueGrid.appendChild(col);
        });

        const n = uploadQueue.length;
        countEl.textContent = n;
        btnUp.disabled = (n === 0);
        if (n > 0) { queueWrap.classList.remove('d-none'); }
        else       { queueWrap.classList.add('d-none'); progWrap.classList.add('d-none'); }
    }

    /* — Subir todos — */
    btnUp.addEventListener('click', async function () {
        if (!uploadQueue.length) return;
        btnUp.disabled = true;
        zone.style.pointerEvents = 'none';
        progWrap.classList.remove('d-none');

        const snapshot = [...uploadQueue]; // copia fija para iterar sin desincronizar índices
        const total    = snapshot.length;
        let ok = 0, fail = 0;

        for (let i = 0; i < total; i++) {
            const item = snapshot[i];
            progText.textContent = `Subiendo ${i + 1} de ${total}: ${item.file.name}`;
            progBar.style.width  = Math.round(i / total * 100) + '%';

            /* Spinner en la primera miniatura visible */
            const thumb = queueGrid.querySelector('.uq-thumb');
            if (thumb) {
                let st = thumb.querySelector('.uq-status');
                if (!st) { st = document.createElement('div'); st.className = 'uq-status'; thumb.appendChild(st); }
                st.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
            }

            const fd = new FormData();
            fd.append('file', item.file);

            try {
                const r = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
                const d = await r.json();
                if (d.success) {
                    ok++;
                    addCardToGrid(d); // insertar tarjeta en tiempo real
                } else {
                    fail++;
                }
            } catch (e) { fail++; }

            if (item.objectUrl) URL.revokeObjectURL(item.objectUrl);
            uploadQueue.shift();
            renderQueue();
            progBar.style.width = Math.round((i + 1) / total * 100) + '%';
        }

        progBar.style.width  = '100%';
        progText.textContent = `✓ ${ok} subido(s)` + (fail ? ` · ${fail} con error` : '');

        /* Ocultar progreso tras 2 s, restaurar zona */
        setTimeout(() => {
            progWrap.classList.add('d-none');
            progBar.style.width = '0%';
            zone.style.pointerEvents = '';
        }, 2000);
    });

    /* ── Construir tarjeta y añadirla al grid sin recargar ── */
    function addCardToGrid(d) {
        /* Si la biblioteca estaba vacía, ocultar el empty-state y crear el grid */
        const emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.style.display = 'none';

        let grid = document.getElementById('media-grid');
        if (!grid) {
            grid = document.createElement('div');
            grid.className = 'media-grid';
            grid.id = 'media-grid';
            /* Insertar antes de la paginación o al final de col-lg-9 */
            const col = document.querySelector('.col-lg-9');
            col.appendChild(grid);
        }

        const sizeKb  = Math.round((d.file_size || 0) / 1024);
        const sizeTxt = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + 'MB' : sizeKb + 'KB';
        const ext     = d.name.split('.').pop().toUpperCase();
        const typeIconMap = { image:'bi-image', video:'bi-camera-video', audio:'bi-music-note-beamed', document:'bi-file-pdf', other:'bi-file-earmark' };
        const typeIcon    = typeIconMap[d.file_type] || 'bi-file-earmark';
        const dimsHtml    = (d.width && d.height) ? ` · ${d.width}×${d.height}px` : '';
        const isImage     = d.file_type === 'image';

        const thumbHtml = isImage
            ? `<img src="${escHtml(d.url)}" alt="${escHtml(d.alt || d.name)}" loading="lazy">`
            : `<div class="d-flex flex-column align-items-center">
                 <i class="bi ${typeIcon} media-icon"></i>
                 <span class="small text-muted mt-1">${escHtml(ext)}</span>
               </div>`;

        const typeLabel = (d.file_type || 'image').charAt(0).toUpperCase() + (d.file_type || 'image').slice(1);

        const tmp = document.createElement('div');
        tmp.innerHTML = `<div class="media-card" data-id="${d.id}" style="animation:fadeIn .3s ease;">
          <div class="card-check"><i class="bi bi-check-lg"></i></div>
          <div class="media-thumb">${thumbHtml}</div>
          <div class="media-info">
            <div class="name" title="${escHtml(d.name)}">${escHtml(d.name)}</div>
            <div class="text-muted">${sizeTxt}${dimsHtml}</div>
            <div><span class="badge type-badge-${escHtml(d.file_type)}">${escHtml(typeLabel)}</span></div>
          </div>
          <div class="media-actions">
            <button type="button" class="btn btn-xs btn-outline-secondary btn-copy-url flex-fill"
                    data-url="${escHtml(d.url)}" title="Copiar URL">
              <i class="bi bi-clipboard"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-primary btn-preview-media"
                    data-url="${escHtml(d.url)}" data-type="${escHtml(d.file_type)}"
                    data-name="${escHtml(d.name)}" data-size="${escHtml(sizeTxt)}"
                    data-dims="${d.width && d.height ? d.width+'×'+d.height+'px' : ''}"
                    title="Ver"><i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-info btn-edit-media"
                    data-id="${d.id}" data-type="${escHtml(d.file_type)}"
                    data-name="${escHtml(d.name)}" data-alt="${escHtml(d.alt||'')}"
                    data-caption="${escHtml(d.caption||'')}" data-url="${escHtml(d.url)}"
                    title="Editar"><i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-danger btn-delete-single"
                    data-id="${d.id}" title="Eliminar">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>`;

        const card = tmp.firstElementChild;
        grid.insertBefore(card, grid.firstChild); // más reciente primero
        wireCardButtons(card);
    }

    /* Conectar los botones de una tarjeta (nueva o existente) */
    function wireCardButtons(card) {
        card.querySelector('.btn-copy-url')?.addEventListener('click', function (e) {
            e.stopPropagation();
            navigator.clipboard.writeText(this.dataset.url).then(() => {
                const orig = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check-lg"></i>';
                setTimeout(() => this.innerHTML = orig, 1500);
            });
        });

        card.querySelector('.btn-preview-media')?.addEventListener('click', function (e) {
            e.stopPropagation();
            openPreviewModal(this.dataset);
        });

        card.querySelector('.btn-edit-media')?.addEventListener('click', function (e) {
            e.stopPropagation();
            openEditModal(this.dataset);
        });

        card.querySelector('.btn-delete-single')?.addEventListener('click', function (e) {
            e.stopPropagation();
            const id = this.dataset.id;
            Swal.fire({
                icon:'warning', title:'¿Eliminar archivo?',
                text:'Esta acción no se puede deshacer.',
                showCancelButton:true, confirmButtonText:'Sí, eliminar',
                cancelButtonText:'Cancelar', confirmButtonColor:'#dc3545',
            }).then(r => {
                if (r.isConfirmed) window.location.href = '?delete=' + id + '&<?= $qsPag ?>&p=<?= $page ?>';
            });
        });

        /* Selección Ctrl+clic */
        card.addEventListener('click', function (e) {
            if (e.target.closest('.media-actions')) return;
            if (!e.ctrlKey && !e.metaKey) return;
            e.preventDefault();
            const id = this.dataset.id;
            if (selectedIds.has(id)) {
                selectedIds.delete(id);
                this.classList.remove('selected');
            } else {
                selectedIds.add(id);
                this.classList.add('selected');
            }
            updateBatchBar();
        });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ════════════════════════════
       MULTISELECT CON Ctrl/Cmd
    ════════════════════════════ */
    const selectedIds = new Set();
    const batchBar    = document.getElementById('batch-bar');
    const batchCount  = document.getElementById('batch-count');

    function updateBatchBar() {
        const n = selectedIds.size;
        if (n > 0) {
            batchBar.classList.add('visible');
            batchCount.textContent = n + ' seleccionada' + (n > 1 ? 's' : '');
        } else {
            batchBar.classList.remove('visible');
        }
    }

    /* — Limpiar selección — */
    document.getElementById('btn-batch-clear')?.addEventListener('click', () => {
        selectedIds.clear();
        document.querySelectorAll('.media-card.selected').forEach(c => c.classList.remove('selected'));
        updateBatchBar();
    });

    /* — Eliminar en lote — */
    document.getElementById('btn-batch-delete')?.addEventListener('click', () => {
        if (!selectedIds.size) return;
        Swal.fire({
            icon             : 'warning',
            title            : '¿Eliminar ' + selectedIds.size + ' archivo(s)?',
            text             : 'Esta acción no se puede deshacer.',
            showCancelButton : true,
            confirmButtonText: 'Sí, eliminar todo',
            cancelButtonText : 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;

            fetch('delete_batch.php', {
                method  : 'POST',
                headers : { 'Content-Type': 'application/json' },
                body    : JSON.stringify({ ids: [...selectedIds] }),
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire({
                        icon:'success', title:'¡Eliminados!', text: d.message,
                        timer:1800, showConfirmButton:false,
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Error', d.message, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
        });
    });

    /* ── Funciones compartidas para modales ── */
    const typeIcons = { video:'bi-camera-video', audio:'bi-music-note-beamed', document:'bi-file-pdf', other:'bi-file-earmark' };

    function openPreviewModal(ds) {
        const { url, type, name, size, dims } = ds;
        document.getElementById('preview-modal-name').textContent = name;
        document.getElementById('preview-modal-link').href        = url;
        document.getElementById('preview-modal-meta').textContent = size + (dims ? ' · ' + dims : '');
        const body = document.getElementById('preview-modal-body');
        if (type === 'image') {
            body.innerHTML = `<img src="${url}" class="img-fluid rounded" style="max-height:70vh" alt="${name}">`;
        } else if (type === 'video') {
            body.innerHTML = `<video controls class="w-100" style="max-height:70vh"><source src="${url}"></video>`;
        } else if (type === 'audio') {
            body.innerHTML = `<div class="py-4"><i class="bi bi-music-note-beamed" style="font-size:64px;color:#9d174d"></i><div class="mt-3"><audio controls class="w-100"><source src="${url}"></audio></div></div>`;
        } else if (type === 'document') {
            body.innerHTML = `<div class="py-4"><i class="bi bi-file-pdf" style="font-size:64px;color:#92400e"></i><div class="mt-3"><a href="${url}" target="_blank" class="btn btn-outline-danger"><i class="bi bi-box-arrow-up-right me-1"></i>Abrir PDF</a></div></div>`;
        } else {
            body.innerHTML = `<div class="py-4"><i class="bi bi-file-earmark" style="font-size:64px;color:#6c757d"></i></div>`;
        }
        new bootstrap.Modal(document.getElementById('modalPreviewMedia')).show();
    }

    function openEditModal(ds) {
        const { id, type, url, alt, caption, name } = ds;
        document.getElementById('modal-id').value      = id;
        document.getElementById('modal-alt').value     = alt || '';
        document.getElementById('modal-caption').value = caption || '';
        document.getElementById('modal-url').value     = url;
        const imgWrap  = document.getElementById('edit-preview-wrap');
        const iconWrap = document.getElementById('edit-preview-icon');
        if (type === 'image') {
            document.getElementById('edit-preview-img').src = url;
            imgWrap.classList.remove('d-none');
            iconWrap.classList.add('d-none');
        } else {
            document.getElementById('edit-preview-icon-el').className = 'bi ' + (typeIcons[type] || 'bi-file-earmark') + ' fs-1 text-muted';
            document.getElementById('edit-preview-icon-name').textContent = name || '';
            iconWrap.classList.remove('d-none');
            imgWrap.classList.add('d-none');
        }
        new bootstrap.Modal(document.getElementById('modalEditMedia')).show();
    }

    /* Conectar botones de todas las tarjetas ya existentes en el DOM */
    document.querySelectorAll('.media-card').forEach(wireCardButtons);

    document.querySelector('.btn-copy-url-modal')?.addEventListener('click', function () {
        navigator.clipboard.writeText(document.getElementById('modal-url').value);
        this.innerHTML = '<i class="bi bi-check-lg"></i>';
        setTimeout(() => this.innerHTML = '<i class="bi bi-clipboard"></i>', 1500);
    });

    document.getElementById('btn-save-media')?.addEventListener('click', function () {
        fetch('update_media.php', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({
                id      : document.getElementById('modal-id').value,
                alt_text: document.getElementById('modal-alt').value,
                caption : document.getElementById('modal-caption').value,
            }),
        })
        .then(r => r.json())
        .then(d => {
            bootstrap.Modal.getInstance(document.getElementById('modalEditMedia')).hide();
            Swal.fire({ icon: d.success?'success':'error', title: d.message, timer:1500, showConfirmButton:false });
        });
    });

    /* ── Limpiar backdrop modales ── */
    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal:visible').length === 0) {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow','').css('padding-right','');
        }
    });

    /* ── Escanear archivos ── */
    document.getElementById('btn-scan-files')?.addEventListener('click', function () {
        const btn      = this;
        const progress = document.getElementById('scan-progress');
        const result   = document.getElementById('scan-result-info');

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Escaneando...';
        progress.classList.remove('d-none');
        result.classList.add('d-none');

        fetch('scan_files.php')
            .then(r => r.json())
            .then(d => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-search me-1"></i> Escanear archivos';
                progress.classList.add('d-none');
                result.classList.remove('d-none');
                result.innerHTML = d.success
                    ? `<div class="alert alert-success py-2 small">
                         <i class="bi bi-check-circle me-1"></i>
                         <strong>${d.inserted}</strong> nuevos registrados<br>
                         <span class="text-muted">${d.skipped} ya existían</span>
                         ${d.errors ? '<br><span class="text-danger">'+d.errors+' errores</span>' : ''}
                       </div>`
                    : `<div class="alert alert-danger py-2 small">${d.message}</div>`;
                if (d.inserted > 0) setTimeout(() => location.reload(), 1800);
            })
            .catch(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-search me-1"></i> Escanear archivos';
                progress.classList.add('d-none');
                result.innerHTML = '<div class="alert alert-danger py-2 small">Error de conexión.</div>';
                result.classList.remove('d-none');
            });
    });

});
</script>

<?php if (!empty($_SESSION['flash'])): $flashes = $_SESSION['flash']; unset($_SESSION['flash']); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const queue   = <?= json_encode($flashes, JSON_UNESCAPED_UNICODE) ?>;
    const iconMap = { success:'success', error:'error', warning:'warning', info:'info' };
    (async () => {
        for (const f of queue) {
            await Swal.fire({ icon: iconMap[f.type]||'info', title: f.msg, confirmButtonText:'OK' });
        }
    })();
});
</script>
<?php endif; ?>
</body>
</html>
