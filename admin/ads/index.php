<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Manejar Publicidad';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
db()->exec("SET NAMES utf8mb4");

// ── Determinar si Auto Ads está activo ────────────────────────────────────
$autoAdsActivo = !empty($sys['adsense_auto_ads']) && $sys['adsense_auto_ads'] == '1';
$pubId         = trim($sys['adsense_publisher_id'] ?? '');

/* ========= Eliminar bloque fijo ========= */
if (isset($_GET['delete'])) {
    $pos = (int)$_GET['delete'];
    db()->prepare("DELETE FROM ads WHERE position=?")->execute([$pos]);
    setFlash('success', "Bloque $pos eliminado.");
    header("Location: index.php#block$pos");
    exit;
}

/* ========= Eliminar de galería ========= */
if (isset($_GET['delete_gallery'])) {
    $id      = (int)($_GET['delete_gallery'] ?? 0);
    $section = (int)($_GET['section'] ?? 3);
    db()->prepare("DELETE FROM ads_gallery WHERE id=?")->execute([$id]);
    setFlash('success', "Banner eliminado.");
    header("Location: index.php#gallery$section");
    exit;
}

/* ========= Eliminar bloque AdSense ========= */
if (isset($_GET['delete_adsense'])) {
    $pos = (int)$_GET['delete_adsense'];
    db()->prepare("DELETE FROM ads WHERE position=? AND ad_type='adsense'")->execute([$pos]);
    setFlash('success', 'Bloque AdSense eliminado.');
    header("Location: index.php#adsense");
    exit;
}

/* ========= Guardar bloque fijo imagen (pos 1 y 2) ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position'])) {
    $pos    = (int)$_POST['position'];
    $title  = trim($_POST['title'] ?? '');
    $url    = trim($_POST['target_url'] ?? '');
    $status = $_POST['status'] ?? 'inactive';
    $imageUrl = null;

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $dir = __DIR__ . '/../../public/images/ads/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file = "ad_{$pos}_" . time() . ".$ext";
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $file)) {
                $imageUrl = "/public/images/ads/$file";
            }
        }
    }

    if ($imageUrl) {
        db()->prepare("INSERT INTO ads (position,title,image_url,target_url,status,ad_type)
                       VALUES (?,?,?,?,?,'image')
                       ON DUPLICATE KEY UPDATE
                       title=VALUES(title),target_url=VALUES(target_url),
                       status=VALUES(status),image_url=VALUES(image_url),ad_type='image'")
            ->execute([$pos, $title, $imageUrl, $url, $status]);
    } else {
        db()->prepare("INSERT INTO ads (position,title,target_url,status,ad_type)
                       VALUES (?,?,?,?,'image')
                       ON DUPLICATE KEY UPDATE
                       title=VALUES(title),target_url=VALUES(target_url),status=VALUES(status)")
            ->execute([$pos, $title, $url, $status]);
    }

    setFlash('success', "Bloque $pos actualizado.");
    header("Location: index.php#block$pos");
    exit;
}

/* ========= Guardar bloques AdSense ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_adsense'])) {
    $slots = $_POST['adsense_slots'] ?? [];

    foreach ($slots as $slotData) {
        $pos    = (int)($slotData['position'] ?? 0);
        $label  = trim($slotData['label']    ?? '');
        $slot   = trim($slotData['slot_id']  ?? '');
        $format = trim($slotData['format']   ?? 'auto');
        $status = $slotData['status']        ?? 'inactive';

        if ($pos <= 0 || empty($slot)) continue;

        $adCode = json_encode([
            'slot_id' => $slot,
            'format'  => $format,
            'pub_id'  => $pubId,
            'css_selector' => trim($slotData['css_selector'] ?? ''),
        ]);

        db()->prepare("INSERT INTO ads (position,title,image_url,target_url,status,ad_type,ad_code)
                       VALUES (?,?,NULL,NULL,?,'adsense',?)
                       ON DUPLICATE KEY UPDATE
                       title=VALUES(title),status=VALUES(status),
                       ad_type='adsense',ad_code=VALUES(ad_code)")
            ->execute([$pos, $label, $status, $adCode]);
    }

    setFlash('success', 'Bloques de AdSense guardados.');
    header("Location: index.php#adsense");
    exit;
}

/* ========= Guardar múltiples en galería ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery'])) {
    $section = (int)$_POST['section'];

    foreach (['horizontal','square'] as $type) {
        $f = $_FILES["image_{$type}_{$section}"] ?? null;
        if (!$f || empty($f['name'][0])) continue;

        foreach ($f['name'] as $i => $name) {
            if ($f['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext     = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) continue;

            $dir  = __DIR__ . '/../../public/images/ads/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file = "gallery{$section}_" . time() . "_$i.$ext";
            if (!move_uploaded_file($f['tmp_name'][$i], $dir . $file)) continue;

            $urlDest = trim($_POST["url_{$type}_{$section}"][$i] ?? '');
            $status  = $_POST["status_{$type}_{$section}"][$i] ?? 'inactive';

            db()->prepare("INSERT INTO ads_gallery (section,title,type,image_url,target_url,status)
                           VALUES (?,?,?,?,?,?)")
                ->execute([$section, '', $type, "/public/images/ads/$file", $urlDest, $status]);
        }
    }

    setFlash('success', "Banners guardados en Sección $section.");
    header("Location: index.php#gallery$section");
    exit;
}

/* ========= Cargar datos ========= */
$ads = [];
foreach ([1, 2] as $pos) {
    $st = db()->prepare("SELECT * FROM ads WHERE position=? LIMIT 1");
    $st->execute([$pos]);
    $ads[$pos] = $st->fetch(PDO::FETCH_ASSOC);
}

$adsenseBlocks = db()->query(
    "SELECT * FROM ads WHERE ad_type='adsense' ORDER BY position ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$gallery3 = db()->query("SELECT * FROM ads_gallery WHERE section=3 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$gallery4 = db()->query("SELECT * FROM ads_gallery WHERE section=4 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$gallery5 = db()->query("SELECT * FROM ads_gallery WHERE section=5 AND type='square' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Gestión de Ads</title>
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .muted { color: #6c757d; }
    .btn-xs { padding: 2px 8px; font-size: 12px; }
  </style>
</head>
<body>

<div class="container" style="padding:0; background:rgba(0,0,0,0)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-layout-text-window-reverse"></i> Gestión de Ads</h1>
  </div>
</div>

<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container py-4">
  <?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

  <!-- NAV TABS -->
  <ul class="nav nav-tabs" role="tablist" id="adsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#block1">Sección 1: Header 1</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#block2">Sección 2: Header 2</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#gallery3">Sección 3: Galería Ads</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#gallery4">Sección 4: Galería Ads</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#gallery5">Sección 5: Slider Cuadrados</a></li>
    <?php if (!$autoAdsActivo): ?>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#adsense">
        <i class="fa-brands fa-google me-1"></i> Google AdSense
      </a>
    </li>
    <?php endif; ?>
  </ul>

  <div class="tab-content p-3 border border-top-0">

    <!-- ══ Sección 1 y 2 ══ -->
    <?php foreach ([1 => "Header 1 (700x70)", 2 => "Header 2 (700x70)"] as $pos => $label):
      $ad = $ads[$pos] ?? []; ?>
      <div class="tab-pane fade <?= $pos == 1 ? 'show active' : '' ?>" id="block<?= $pos ?>">
        <div class="card mb-3">
          <div class="card-header bg-light"><strong><?= $label ?></strong></div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="position" value="<?= $pos ?>">
              <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control"
                       value="<?= htmlspecialchars($ad['title'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Imagen (sugerido 700x70)</label><br>
                <?php if (!empty($ad['image_url'])): ?>
                  <img src="<?= $ad['image_url'] ?>" style="max-height:70px" class="mb-2"><br>
                  <a href="?delete=<?= $pos ?>" class="btn btn-sm btn-danger mt-2"
                     onclick="return confirm('¿Eliminar este banner?')">
                    <i class="bi bi-trash-fill"></i> Eliminar
                  </a>
                <?php endif; ?>
                <input type="file" name="image" class="form-control mt-2"
                       accept=".jpg,.jpeg,.png,.gif,.webp">
              </div>
              <div class="mb-3">
                <label class="form-label">URL destino</label>
                <input type="url" name="target_url" class="form-control"
                       value="<?= htmlspecialchars($ad['target_url'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                  <option value="active"   <?= ($ad['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Activo</option>
                  <option value="inactive" <?= ($ad['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                </select>
              </div>
              <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Guardar
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- ══ Sección 3 ══ -->
    <div class="tab-pane fade" id="gallery3">
      <?= render_gallery_section(3, $gallery3) ?>
    </div>

    <!-- ══ Sección 4 ══ -->
    <div class="tab-pane fade" id="gallery4">
      <?= render_gallery_section(4, $gallery4) ?>
    </div>

    <!-- ══ Sección 5: Slider Cuadrados ══ -->
    <div class="tab-pane fade" id="gallery5">
      <div class="alert alert-info py-2">
        Banners <strong>cuadrados</strong> para el slider.
      </div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="save_gallery" value="1">
        <input type="hidden" name="section" value="5">
        <table class="table table-bordered align-middle" id="table-square-5">
          <thead><tr><th>Imagen</th><th>URL</th><th>Estado</th><th></th></tr></thead>
          <tbody>
            <?php if ($gallery5): foreach ($gallery5 as $row): ?>
              <tr>
                <td><img src="<?= $row['image_url'] ?>" style="max-height:90px"></td>
                <td><?= htmlspecialchars($row['target_url'] ?? '') ?></td>
                <td><?= $row['status'] === 'active'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                <td>
                  <a href="?delete_gallery=<?= $row['id'] ?>&section=5"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('¿Eliminar banner?')">
                    <i class="bi bi-trash-fill"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center muted">No hay banners aún.</td></tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-center">
                <button type="button" class="btn btn-outline-primary btn-add-row"
                        data-type="square" data-section="5">
                  <i class="bi bi-plus-circle"></i> Agregar Banner Cuadrado
                </button>
              </td>
            </tr>
          </tfoot>
        </table>
        <div class="text-end mt-3">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Guardar nuevos
          </button>
        </div>
      </form>
    </div>

    <!-- ══ Sección Google AdSense (solo si Auto Ads está OFF) ══ -->
    <?php if (!$autoAdsActivo): ?>
    <div class="tab-pane fade" id="adsense">

  
<button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefreshZoneMap">
    <i class="bi bi-arrow-clockwise"></i> Regenerar mapa de zonas
</button>

<script>
document.getElementById('btnRefreshZoneMap')?.addEventListener('click', function () {
    fetch('clear_ads_cache.php')
        .then(r => r.json())
        .then(d => Swal.fire({ icon: 'success', title: d.message, timer: 1500 }));
});
</script>

      <?php if (empty($pubId)): ?>
        <div class="alert alert-warning mt-3">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>
          No tienes configurado un <strong>Publisher ID</strong>.
          Ve a <a href="../configuraciones/index.php">Configuraciones → SEO</a> y agrégalo primero.
        </div>
      <?php else: ?>
        <div class="alert alert-success py-2 mt-3">
          <i class="fa-brands fa-google me-2"></i>
          Publisher ID activo: <code><?= htmlspecialchars($pubId) ?></code>
        </div>
      <?php endif; ?>

      <div class="card mb-4">
        <div class="card-header bg-light"><strong>¿Cómo funciona?</strong></div>
        <div class="card-body text-muted small">
          <ol class="mb-0">
            <li>En <a href="https://adsense.google.com" target="_blank">Google AdSense</a> ve a <strong>Anuncios → Por bloque de anuncio</strong> y crea un bloque.</li>
            <li>Copia el <strong>Slot ID</strong> (número de 10 dígitos: <code>data-ad-slot="XXXXXXXXXX"</code>).</li>
            <li>Agrégalo aquí con una etiqueta para identificarlo (ej: "Entre noticias", "Sidebar").</li>
            <li>En el template usa <code>&lt;?= renderAdsenseBlock(POSICION) ?&gt;</code> donde quieras el anuncio.</li>
          </ol>
        </div>
      </div>

      <!-- Bloques existentes -->
      <?php if ($adsenseBlocks): ?>
      <div class="card mb-4">
        <div class="card-header bg-light"><strong>Bloques configurados</strong></div>
        <div class="card-body p-0">
          <table class="table table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>Pos.</th>
                <th>Etiqueta</th>
                <th>Slot ID</th>
                <th>Ubicación</th>
                <th>Formato</th>
                <th>Estado</th>
                <th>Código</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($adsenseBlocks as $block):
                $meta = json_decode($block['ad_code'] ?? '{}', true); ?>
              <tr>
                <td><span class="badge bg-secondary"><?= $block['position'] ?></span></td>
                <td><?= htmlspecialchars($block['title']) ?></td>
                <td><code><?= htmlspecialchars($meta['slot_id'] ?? '—') ?></code></td>
                <td><?= htmlspecialchars($meta['format'] ?? 'auto') ?></td>
                <td>
                  <?= $block['status'] === 'active'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>' ?>
                </td>
                <td>
                  <?php if (!empty($meta['slot_id']) && !empty($pubId)): ?>
                  <button type="button" class="btn btn-xs btn-outline-primary btn-preview-ad"
                          data-pub="<?= htmlspecialchars($pubId) ?>"
                          data-slot="<?= htmlspecialchars($meta['slot_id']) ?>"
                          data-format="<?= htmlspecialchars($meta['format'] ?? 'auto') ?>">
                    <i class="bi bi-eye"></i> Ver código
                  </button>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="?delete_adsense=<?= $block['position'] ?>"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('¿Eliminar este bloque?')">
                    <i class="bi bi-trash-fill"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Agregar nuevos bloques -->
      <div class="card">
        <div class="card-header bg-light"><strong>Agregar bloque AdSense</strong></div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="save_adsense" value="1">
            <table class="table table-bordered" id="table-adsense-new">
              <thead class="table-light">
                <tr>
                  <th>Posición</th>
                  <th>Etiqueta</th>
                  <th>Slot ID <small class="text-muted">(data-ad-slot)</small></th>
                  <th>Ubicación</th>
                  <th>Formato</th>
                  <th>Estado</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="adsense-rows"></tbody>
            </table>
            <button type="button" class="btn btn-outline-primary mb-3" id="btn-add-adsense">
              <i class="bi bi-plus-circle"></i> Agregar bloque
            </button>
            <div class="text-end">
              <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Guardar bloques
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal preview -->
      <div class="modal fade" id="modalAdCode" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Código del bloque</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <pre id="adCodePreview" class="bg-light p-3 rounded"
                   style="font-size:12px; overflow-x:auto;"></pre>
              <button class="btn btn-sm btn-outline-secondary mt-2" id="btnCopyAdCode">
                <i class="bi bi-clipboard"></i> Copiar
              </button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /adsense -->
    <?php endif; ?>

  </div><!-- /tab-content -->
</div>

<?php
/* ══ HELPER: render galería ══════════════════════════════════════════════ */
function render_gallery_section(int $section, array $gallery) {
    ob_start(); ?>
    <div class="alert alert-info py-2">
        Banners <strong>horizontales</strong> o <strong>cuadrados</strong>.
    </div>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="save_gallery" value="1">
        <input type="hidden" name="section" value="<?= $section ?>">

        <?php foreach (['horizontal' => 'Horizontales', 'square' => 'Cuadrados'] as $type => $label):
            $maxH = $type === 'horizontal' ? '70px' : '90px'; ?>
        <h5 class="mt-2">Banners <?= $label ?></h5>
        <table class="table table-bordered align-middle" id="table-<?= $type ?>-<?= $section ?>">
            <thead><tr><th>Imagen</th><th>URL</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php $hasRows = false;
                foreach ($gallery as $row):
                    if ($row['type'] !== $type) continue; $hasRows = true; ?>
                    <tr>
                        <td><img src="<?= $row['image_url'] ?>" style="max-height:<?= $maxH ?>"></td>
                        <td><?= htmlspecialchars($row['target_url'] ?? '') ?></td>
                        <td><?= $row['status'] === 'active'
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                        <td>
                            <a href="?delete_gallery=<?= $row['id'] ?>&section=<?= $section ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Eliminar?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach;
                if (!$hasRows): ?>
                    <tr><td colspan="4" class="text-center muted">Sin banners aún.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-center">
                        <button type="button" class="btn btn-outline-primary btn-add-row"
                                data-type="<?= $type ?>" data-section="<?= $section ?>">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php endforeach; ?>

        <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Guardar nuevos
            </button>
        </div>
    </form>
    <?php return ob_get_clean();
}
?>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* — Activar tab por hash — */
    if (location.hash) {
        const trigger = document.querySelector(`a[href="${location.hash}"]`);
        if (trigger) new bootstrap.Tab(trigger).show();
    }

    /* — Filas dinámicas galería — */
    document.querySelectorAll('.btn-add-row').forEach(btn => {
        btn.addEventListener('click', function () {
            const type    = this.dataset.type;
            const section = this.dataset.section;
            const tbody   = document.querySelector(`#table-${type}-${section} tbody`);
            const row     = document.createElement('tr');
            row.innerHTML = `
                <td><input type="file" name="image_${type}_${section}[]"
                           class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp" required></td>
                <td><input type="url" name="url_${type}_${section}[]" class="form-control"></td>
                <td>
                    <select name="status_${type}_${section}[]" class="form-select">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-sm btn-danger btn-remove-row">
                    <i class="bi bi-trash-fill"></i></button></td>`;
            tbody.appendChild(row);
            row.querySelector('.btn-remove-row').addEventListener('click', () => row.remove());
        });
    });

    /* — Filas dinámicas AdSense — */
    let adsenseCounter = 10;
    <?php foreach ($adsenseBlocks as $b): ?>
    if (<?= (int)$b['position'] ?> >= adsenseCounter) adsenseCounter = <?= (int)$b['position'] ?> + 1;
    <?php endforeach; ?>

    document.getElementById('btn-add-adsense')?.addEventListener('click', function () {
        const tbody = document.getElementById('adsense-rows');
        const pos   = adsenseCounter++;
        const row   = document.createElement('tr');
        row.innerHTML = `
            <td>
                <span class="badge bg-secondary">${pos}</span>
                <input type="hidden" name="adsense_slots[${pos}][position]" value="${pos}">
            </td>
            <td><input type="text" name="adsense_slots[${pos}][label]"
                       class="form-control form-control-sm"
                       placeholder="Ej: Entre noticias"></td>
            <td><input type="text" name="adsense_slots[${pos}][slot_id]"
                       class="form-control form-control-sm font-monospace"
                       placeholder="1234567890"></td>
               
<td>
    <input type="text" name="adsense_slots[${pos}][css_selector]"
           class="form-control form-control-sm font-monospace"
           placeholder="Ej: .news-grid (opcional, sobreescribe zona)">
</td>       
            <td>
                <select name="adsense_slots[${pos}][format]" class="form-select form-select-sm">
                    <option value="auto">Auto</option>
                    <option value="rectangle">Rectángulo</option>
                    <option value="horizontal">Horizontal</option>
                    <option value="vertical">Vertical</option>
                </select>
            </td>
            <td>
                <select name="adsense_slots[${pos}][status]" class="form-select form-select-sm">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-sm btn-danger btn-remove-row">
                <i class="bi bi-trash-fill"></i></button></td>`;
        tbody.appendChild(row);
        row.querySelector('.btn-remove-row').addEventListener('click', () => row.remove());
    });

    /* — Preview código AdSense — */
    document.querySelectorAll('.btn-preview-ad').forEach(btn => {
        btn.addEventListener('click', function () {
            const pub    = this.dataset.pub;
            const slot   = this.dataset.slot;
            const format = this.dataset.format;
            const code   =
`<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="${pub}"
     data-ad-slot="${slot}"
     data-ad-format="${format}"
     data-full-width-responsive="true"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});<\/script>`;
            document.getElementById('adCodePreview').textContent = code;
            new bootstrap.Modal(document.getElementById('modalAdCode')).show();
        });
    });

    /* — Copiar código — */
    document.getElementById('btnCopyAdCode')?.addEventListener('click', function () {
        navigator.clipboard.writeText(
            document.getElementById('adCodePreview').textContent
        ).then(() => {
            this.innerHTML = '<i class="bi bi-check-lg"></i> Copiado';
            setTimeout(() => this.innerHTML = '<i class="bi bi-clipboard"></i> Copiar', 2000);
        });
    });

});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($_SESSION['flash'])): $flashes = $_SESSION['flash']; unset($_SESSION['flash']); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const queue   = <?= json_encode($flashes, JSON_UNESCAPED_UNICODE) ?>;
    const iconMap = { success:'success', error:'error', warning:'warning', info:'info' };
    (async () => {
        for (const f of queue) {
            await Swal.fire({ icon: iconMap[f.type] || 'info', title: f.msg, confirmButtonText: 'OK' });
        }
    })();
});
</script>
<?php endif; ?>
</body>
</html>








