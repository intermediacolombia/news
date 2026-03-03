<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Multimedia';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

db()->exec("SET NAMES utf8mb4");

/* ── Eliminar ── */
if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $file = db()->prepare("SELECT file_path FROM multimedia WHERE id = ? AND deleted = 0 LIMIT 1");
    $file->execute([$id]);
    $row  = $file->fetch();
    if ($row) {
        $abs = __DIR__ . '/../../' . ltrim($row['file_path'], '/');
        if (file_exists($abs)) unlink($abs);
        db()->prepare("UPDATE multimedia SET deleted = 1 WHERE id = ?")->execute([$id]);
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

    $mime = mime_content_type($file['tmp_name']);
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

/* ── Filtros ── */
$filterType = $_GET['type'] ?? '';
$filterQ    = trim($_GET['q'] ?? '');
$page       = max(1, (int)($_GET['p'] ?? 1));
$perPage    = 24;
$offset     = ($page - 1) * $perPage;

$where  = "WHERE deleted = 0";
$params = [];
if ($filterType) { $where .= " AND file_type = ?"; $params[] = $filterType; }
if ($filterQ)    { $where .= " AND (file_name LIKE ? OR alt_text LIKE ?)"; $params[] = "%$filterQ%"; $params[] = "%$filterQ%"; }

$total = db()->prepare("SELECT COUNT(*) FROM multimedia $where");
$total->execute($params);
$totalFiles = $total->fetchColumn();
$totalPages = ceil($totalFiles / $perPage);

$stmt = db()->prepare("SELECT * FROM multimedia $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    .media-card { border:1px solid #dee2e6; border-radius:8px; overflow:hidden; background:#fff; position:relative; }
    .media-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.15); }
    .media-thumb { width:100%; height:140px; background:#f8f9fa; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .media-thumb img { width:100%; height:140px; object-fit:cover; }
    .media-thumb .media-icon { font-size:48px; color:#6c757d; }
    .media-info { padding:8px; font-size:12px; }
    .media-info .name { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .media-actions { display:flex; gap:4px; padding:0 8px 8px; }
    .upload-zone { border:2px dashed #dee2e6; border-radius:8px; padding:40px; text-align:center; cursor:pointer; transition:.2s; }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#0d6efd; background:#f0f4ff; }
    .type-badge-image    { background:#d1fae5; color:#065f46; }
    .type-badge-video    { background:#dbeafe; color:#1e40af; }
    .type-badge-audio    { background:#fce7f3; color:#9d174d; }
    .type-badge-document { background:#fef3c7; color:#92400e; }
    .type-badge-other    { background:#f3f4f6; color:#374151; }
    .btn-xs { padding:2px 8px; font-size:12px; }
  </style>
</head>
<body>

<div class="container" style="padding:0;background:rgba(0,0,0,0)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-images"></i> Multimedia</h1>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container py-4">
  <?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

  <div class="row g-4">

    <!-- ══ Columna izquierda ══ -->
    <div class="col-lg-3">

      <!-- Subir -->
      <div class="card mb-3">
        <div class="card-header bg-light"><strong><i class="bi bi-cloud-upload me-2"></i>Subir archivo</strong></div>
        <div class="card-body">
          <form method="post" enctype="multipart/form-data" id="upload-form">
            <div class="upload-zone mb-3" id="upload-zone" onclick="document.getElementById('file-input').click()">
              <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
              <div class="mt-2 text-muted small">Arrastra o haz clic para subir</div>
              <div class="text-muted" style="font-size:11px">JPG, PNG, WebP, GIF, MP4, MP3, PDF — Máx 20MB</div>
            </div>
            <input type="file" id="file-input" name="file" class="d-none"
                   accept="image/*,video/mp4,video/webm,audio/mpeg,audio/wav,application/pdf">
            <div id="file-preview" class="mb-3 d-none text-center">
              <img id="preview-img" src="" class="img-thumbnail" style="max-height:100px">
              <div id="preview-name" class="small text-muted mt-1"></div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Texto alternativo (alt)</label>
              <input type="text" name="alt_text" class="form-control form-control-sm" placeholder="Descripción de la imagen">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Caption</label>
              <textarea name="caption" class="form-control form-control-sm" rows="2" placeholder="Pie de foto opcional"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btn-upload" disabled>
              <i class="bi bi-upload me-1"></i> Subir
            </button>
          </form>
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

      <!-- Escanear — DENTRO del col-lg-3 -->
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

      <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
        <input type="text" name="q" class="form-control form-control-sm" style="max-width:200px"
               placeholder="Buscar..." value="<?= htmlspecialchars($filterQ) ?>">
        <select name="type" class="form-select form-select-sm" style="max-width:140px" onchange="this.form.submit()">
          <option value="">Todos los tipos</option>
          <?php foreach (['image'=>'Imágenes','video'=>'Videos','audio'=>'Audio','document'=>'Documentos','other'=>'Otros'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= $filterType === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        <?php if ($filterType || $filterQ): ?>
        <a href="index.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i> Limpiar</a>
        <?php endif; ?>
        <span class="ms-auto text-muted small align-self-center"><?= $totalFiles ?> archivos</span>
      </form>

      <?php if (empty($files)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-images fs-1"></i>
        <div class="mt-2">No hay archivos aún</div>
      </div>
      <?php else: ?>
      <div class="media-grid">
        <?php foreach ($files as $f):
          $isImage   = $f['file_type'] === 'image';
          $fileUrl   = URLBASE . '/' . $f['file_path']; // ← construir URL aquí
          $sizeKb    = round($f['file_size'] / 1024);
          $sizeTxt   = $sizeKb > 1024 ? round($sizeKb/1024,1).'MB' : $sizeKb.'KB';
          $typeIcons = ['image'=>'bi-image','video'=>'bi-camera-video','audio'=>'bi-music-note-beamed','document'=>'bi-file-pdf','other'=>'bi-file-earmark'];
        ?>
        <div class="media-card">
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
            <a href="?delete=<?= $f['id'] ?>" class="btn btn-xs btn-outline-danger"
               onclick="return confirm('¿Eliminar archivo?')" title="Eliminar">
              <i class="bi bi-trash"></i>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1):
    $range  = 2; // páginas a cada lado de la actual
    $start  = max(1, $page - $range);
    $end    = min($totalPages, $page + $range);
    $qs     = '&type=' . urlencode($filterType) . '&q=' . urlencode($filterQ);
?>
<nav class="mt-4">
  <ul class="pagination pagination-sm justify-content-center flex-wrap">

    <!-- Primera -->
    <?php if ($start > 1): ?>
    <li class="page-item">
      <a class="page-link" href="?p=1<?= $qs ?>">«</a>
    </li>
    <?php if ($start > 2): ?>
    <li class="page-item disabled"><span class="page-link">…</span></li>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Páginas alrededor -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
      <a class="page-link" href="?p=<?= $i ?><?= $qs ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>

    <!-- Última -->
    <?php if ($end < $totalPages): ?>
    <?php if ($end < $totalPages - 1): ?>
    <li class="page-item disabled"><span class="page-link">…</span></li>
    <?php endif; ?>
    <li class="page-item">
      <a class="page-link" href="?p=<?= $totalPages ?><?= $qs ?>">»</a>
    </li>
    <?php endif; ?>

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
          <a id="preview-modal-link" href="#" target="_blank"
             class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-arrow-up-right"></i> Abrir
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body text-center p-2" id="preview-modal-body">
        <!-- contenido dinámico -->
      </div>
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

        <!-- Preview pequeño -->
        <div id="edit-preview-wrap" class="mb-3 text-center d-none">
          <img id="edit-preview-img" src="" alt=""
               class="img-thumbnail" style="max-height:80px; max-width:100%;">
        </div>
        <div id="edit-preview-icon" class="mb-3 text-center d-none">
          <i id="edit-preview-icon-el" class="bi fs-1 text-muted"></i>
          <div class="small text-muted mt-1" id="edit-preview-icon-name"></div>
        </div>

        <!-- URL -->
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

    /* — Upload drag & drop — */
    const zone  = document.getElementById('upload-zone');
    const input = document.getElementById('file-input');
    const btn   = document.getElementById('btn-upload');

    ['dragenter','dragover'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('drag-over'); }));
    ['dragleave','drop'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.remove('drag-over'); }));
    zone.addEventListener('drop', ev => { input.files = ev.dataTransfer.files; handleFileSelect(); });
    input.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        if (!input.files.length) return;
        const file = input.files[0];
        const preview = document.getElementById('file-preview');
        const img     = document.getElementById('preview-img');
        const name    = document.getElementById('preview-name');
        name.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + 'KB)';
        preview.classList.remove('d-none');
        btn.disabled = false;
        if (file.type.startsWith('image/')) {
            img.src = URL.createObjectURL(file);
            img.classList.remove('d-none');
        } else {
            img.classList.add('d-none');
        }
    }

    /* — Copiar URL tarjetas — */
    document.querySelectorAll('.btn-copy-url').forEach(b => {
        b.addEventListener('click', function () {
            navigator.clipboard.writeText(this.dataset.url).then(() => {
                const orig = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check-lg"></i>';
                setTimeout(() => this.innerHTML = orig, 1500);
            });
        });
    });

    /* — Modal PREVIEW — */
    const typeIcons = {
        video    : 'bi-camera-video',
        audio    : 'bi-music-note-beamed',
        document : 'bi-file-pdf',
        other    : 'bi-file-earmark',
    };

    document.querySelectorAll('.btn-preview-media').forEach(b => {
        b.addEventListener('click', function () {
            const url   = this.dataset.url;
            const type  = this.dataset.type;
            const name  = this.dataset.name;
            const size  = this.dataset.size;
            const dims  = this.dataset.dims;

            document.getElementById('preview-modal-name').textContent = name;
            document.getElementById('preview-modal-link').href        = url;
            document.getElementById('preview-modal-meta').textContent =
                size + (dims ? ' · ' + dims : '');

            const body = document.getElementById('preview-modal-body');

            if (type === 'image') {
                body.innerHTML = `<img src="${url}" class="img-fluid rounded"
                                       style="max-height:70vh" alt="${name}">`;
            } else if (type === 'video') {
                body.innerHTML = `<video controls class="w-100" style="max-height:70vh">
                                    <source src="${url}">
                                  </video>`;
            } else if (type === 'audio') {
                body.innerHTML = `<div class="py-4">
                                    <i class="bi bi-music-note-beamed" style="font-size:64px;color:#9d174d"></i>
                                    <div class="mt-3">
                                      <audio controls class="w-100"><source src="${url}"></audio>
                                    </div>
                                  </div>`;
            } else if (type === 'document') {
                body.innerHTML = `<div class="py-4">
                                    <i class="bi bi-file-pdf" style="font-size:64px;color:#92400e"></i>
                                    <div class="mt-3">
                                      <a href="${url}" target="_blank" class="btn btn-outline-danger">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Abrir PDF
                                      </a>
                                    </div>
                                  </div>`;
            } else {
                body.innerHTML = `<div class="py-4">
                                    <i class="bi bi-file-earmark" style="font-size:64px;color:#6c757d"></i>
                                  </div>`;
            }

            new bootstrap.Modal(document.getElementById('modalPreviewMedia')).show();
        });
    });

    /* — Modal EDITAR — */
    document.querySelectorAll('.btn-edit-media').forEach(b => {
        b.addEventListener('click', function () {
            const type = this.dataset.type;
            const url  = this.dataset.url;

            document.getElementById('modal-id').value      = this.dataset.id;
            document.getElementById('modal-alt').value     = this.dataset.alt;
            document.getElementById('modal-caption').value = this.dataset.caption;
            document.getElementById('modal-url').value     = url;

            const imgWrap  = document.getElementById('edit-preview-wrap');
            const iconWrap = document.getElementById('edit-preview-icon');

            if (type === 'image') {
                document.getElementById('edit-preview-img').src = url;
                imgWrap.classList.remove('d-none');
                iconWrap.classList.add('d-none');
            } else {
                const iconEl   = document.getElementById('edit-preview-icon-el');
                const iconName = document.getElementById('edit-preview-icon-name');
                iconEl.className   = 'bi ' + (typeIcons[type] || 'bi-file-earmark') + ' fs-1 text-muted';
                iconName.textContent = this.dataset.name;
                iconWrap.classList.remove('d-none');
                imgWrap.classList.add('d-none');
            }

            new bootstrap.Modal(document.getElementById('modalEditMedia')).show();
        });
    });

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
            Swal.fire({ icon: d.success ? 'success' : 'error', title: d.message, timer: 1500, showConfirmButton: false });
        });
    });

    /* — Escanear archivos — */
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
                         ${d.errors ? '<br><span class="text-danger">' + d.errors + ' errores</span>' : ''}
                       </div>`
                    : `<div class="alert alert-danger py-2 small">${d.message}</div>`;
                if (d.inserted > 0) setTimeout(() => location.reload(), 1800);
            })
            .catch(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-search me-1"></i> Escanear archivos';
                progress.classList.add('d-none');
                result.classList.remove('d-none');
                result.innerHTML = '<div class="alert alert-danger py-2 small">Error de conexión.</div>';
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