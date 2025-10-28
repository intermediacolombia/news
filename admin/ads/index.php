<?php
require_once __DIR__ . '/../../inc/config.php';

require_once __DIR__ . '/../login/session.php';  // Inicia la sesión y carga la información del usuario
$permisopage = 'Manejar Publicidad';
require_once __DIR__ . '/../login/restriction.php';
session_start();

require_once __DIR__ . '/../inc/flash_helpers.php';

if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
$pdo->exec("SET NAMES utf8mb4");

/* ========= Eliminar bloque fijo (Sección 1 y 2) ========= */
if (isset($_GET['delete'])) {
    $pos = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM ads WHERE position=?")->execute([$pos]);
    setFlash('success', "Bloque $pos eliminado.");
    header("Location: index.php#block$pos");
    exit;
}

/* ========= Eliminar de galería (Sección 3, 4 o 5) ========= */
if (isset($_GET['delete_gallery'])) {
    $id      = (int)($_GET['delete_gallery'] ?? 0);
    $section = (int)($_GET['section'] ?? 3);
    $pdo->prepare("DELETE FROM ads_gallery WHERE id=?")->execute([$id]);
    setFlash('success', "Banner eliminado.");
    header("Location: index.php#gallery$section");
    exit;
}

/* ========= Guardar bloque fijo (1 y 2) ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position'])) {
    $pos     = (int)$_POST['position'];
    $title   = trim($_POST['title'] ?? '');
    $url     = trim($_POST['target_url'] ?? '');
    $status  = $_POST['status'] ?? 'inactive';
    $imageUrl = null;

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext,$allowed)) {
            $dir = __DIR__ . '/../../public/images/ads/';
            if (!is_dir($dir)) mkdir($dir,0777,true);
            $file = "ad_{$pos}_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'],$dir.$file)) {
                $imageUrl = "/public/images/ads/$file";
            }
        }
    }

    if ($imageUrl) {
        $sql = "INSERT INTO ads (position,title,image_url,target_url,status)
                VALUES (?,?,?,?,?)
                ON DUPLICATE KEY UPDATE 
                title=VALUES(title), target_url=VALUES(target_url),
                status=VALUES(status), image_url=VALUES(image_url)";
        $pdo->prepare($sql)->execute([$pos,$title,$imageUrl,$url,$status]);
    } else {
        $sql = "INSERT INTO ads (position,title,target_url,status)
                VALUES (?,?,?,?)
                ON DUPLICATE KEY UPDATE 
                title=VALUES(title), target_url=VALUES(target_url), status=VALUES(status)";
        $pdo->prepare($sql)->execute([$pos,$title,$url,$status]);
    }

    setFlash('success',"Bloque $pos actualizado.");
    header("Location: index.php#block$pos");
    exit;
}

/* ========= Guardar múltiples en galería (Sección 3, 4 o 5) ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery'])) {
    $section = (int)$_POST['section']; // 3, 4 o 5

    foreach (['horizontal','square'] as $type) {
        $f = $_FILES["image_{$type}_{$section}"] ?? null;
        if (!$f || empty($f['name'][0])) continue;

        foreach ($f['name'] as $i=>$name) {
            if ($f['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmp = $f['tmp_name'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext,$allowed)) continue;

            $dir = __DIR__ . '/../../public/images/ads/';
            if (!is_dir($dir)) mkdir($dir,0777,true);
            $file = "gallery{$section}_" . time() . "_$i.$ext";
            if (!move_uploaded_file($tmp, $dir.$file)) continue;

            $url    = trim($_POST["url_{$type}_{$section}"][$i] ?? '');
            $status = $_POST["status_{$type}_{$section}"][$i] ?? 'inactive';

            $pdo->prepare("INSERT INTO ads_gallery (section,title,type,image_url,target_url,status)
                           VALUES (?,?,?,?,?,?)")
                ->execute([$section,'',$type,"/public/images/ads/$file",$url,$status]);
        }
    }
    setFlash('success',"Banners guardados en Sección $section.");
    header("Location: index.php#gallery$section");
    exit;
}

/* ========= Cargar datos ========= */
$ads = [];
foreach ([1,2] as $pos) {
    $st = $pdo->prepare("SELECT * FROM ads WHERE position=? LIMIT 1");
    $st->execute([$pos]);
    $ads[$pos] = $st->fetch(PDO::FETCH_ASSOC);
}
$gallery3 = $pdo->query("SELECT * FROM ads_gallery WHERE section=3 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$gallery4 = $pdo->query("SELECT * FROM ads_gallery WHERE section=4 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$gallery5 = $pdo->query("SELECT * FROM ads_gallery WHERE section=5 AND type='square' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Gestión de Ads</title>
  <?php require_once __DIR__ . '/../inc/header.php'; ?>
  <!-- Asegura íconos Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>.muted{color:#6c757d}</style>
</head>
<body>
<div class="container" style="padding: 0px; background:rgba(0,0,0,0.00)">
  <div class="portada">
    <h1 class="mb-4"><i class="bi bi-layout-text-window-reverse"></i> Gestión de Ads</h1>
  </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="container py-4">
  
  <?php require_once __DIR__ . '/../inc/flash_simple.php'; ?>

  <!-- NAV -->
  <ul class="nav nav-tabs" role="tablist" id="adsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#block1">Sección 1: Header 1</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#block2">Sección 2: Header 2</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#gallery3">Sección 3: Galería Ads</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#gallery4">Sección 4: Galería Ads</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#gallery5">Sección 5: Slider Cuadrados</a></li>
  </ul>

  <div class="tab-content p-3 border border-top-0">
    <!-- ====== Sección 1 y 2 ====== -->
    <?php foreach ([1=>"Header 1 (700x70)",2=>"Header 2 (700x70)"] as $pos=>$label): $ad = $ads[$pos] ?? []; ?>
      <div class="tab-pane fade <?= $pos==1?'show active':'' ?>" id="block<?= $pos ?>">
        <div class="card mb-3">
          <div class="card-header bg-light"><strong><?= $label ?></strong></div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="position" value="<?= $pos ?>">
              <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ad['title'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Imagen (sugerido 700x70)</label><br>
                <?php if (!empty($ad['image_url'])): ?>
                  <img src="<?= $ad['image_url'] ?>" style="max-height:70px" class="mb-2"><br>
                  <a href="?delete=<?= $pos ?>" class="btn btn-sm btn-danger mt-2" onclick="return confirm('¿Eliminar este banner?')">
                    <i class="bi bi-trash-fill"></i> Eliminar
                  </a>
                <?php endif; ?>
                <input type="file" name="image" class="form-control mt-2" accept=".jpg,.jpeg,.png,.gif,.webp">
              </div>
              <div class="mb-3">
                <label class="form-label">URL destino</label>
                <input type="url" name="target_url" class="form-control" value="<?= htmlspecialchars($ad['target_url'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                  <option value="active"   <?= ($ad['status']??'')==='active'?'selected':'' ?>>Activo</option>
                  <option value="inactive" <?= ($ad['status']??'')==='inactive'?'selected':'' ?>>Inactivo</option>
                </select>
              </div>
              <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- ====== Sección 3 ====== -->
    <div class="tab-pane fade" id="gallery3">
      <?php $section=3; $gallery=$gallery3; ?>
      <?= render_gallery_section($section,$gallery); ?>
    </div>

    <!-- ====== Sección 4 ====== -->
    <div class="tab-pane fade" id="gallery4">
      <?php $section=4; $gallery=$gallery4; ?>
      <?= render_gallery_section($section,$gallery); ?>
    </div>

    <!-- ====== Sección 5: Slider Cuadrados ====== -->
    <div class="tab-pane fade" id="gallery5">
      <div class="alert alert-info py-2">
        Esta sección permite administrar solo banners <strong>cuadrados</strong> que se usarán en el slider.
      </div>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="save_gallery" value="1">
        <input type="hidden" name="section" value="5">

        <table class="table table-bordered align-middle" id="table-square-5">
          <thead><tr><th>Imagen</th><th>URL</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody>
            <?php if ($gallery5): ?>
              <?php foreach($gallery5 as $row): ?>
                <tr>
                  <td><img src="<?= $row['image_url'] ?>" style="max-height:90px"></td>
                  <td><?= htmlspecialchars($row['target_url'] ?? '') ?></td>
                  <td><?= ($row['status']==='active')?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>' ?></td>
                  <td>
                    <a href="?delete_gallery=<?= $row['id'] ?>&section=5" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar banner?')">
                      <i class="bi bi-trash-fill"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center muted">No hay banners cuadrados aún.</td></tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-center">
                <button type="button" class="btn btn-outline-primary btn-add-row" data-type="square" data-section="5">
                  <i class="bi bi-plus-circle"></i> Agregar Banner Cuadrado
                </button>
              </td>
            </tr>
          </tfoot>
        </table>

        <div class="text-end mt-3">
          <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar nuevos</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
/* ====== RENDER: tablas de galería (horizontales y cuadrados) ====== */
function render_gallery_section(int $section, array $gallery){
  ob_start(); ?>
  <div class="alert alert-info py-2">
    Puedes agregar banners <strong>horizontales</strong> o <strong>cuadrados</strong> (tamaños libres, sugerencias: 700x70 y 300x300).
  </div>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="save_gallery" value="1">
    <input type="hidden" name="section" value="<?= $section ?>">

    <!-- Horizontales -->
    <h5 class="mt-2">Banners Horizontales</h5>
    <table class="table table-bordered align-middle" id="table-horizontal-<?= $section ?>">
      <thead><tr><th>Imagen</th><th>URL</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php
        $hasRows=false;
        foreach($gallery as $row):
          if ($row['type']!=='horizontal') continue; $hasRows=true; ?>
          <tr>
            <td><img src="<?= $row['image_url'] ?>" style="max-height:70px"></td>
            <td><?= htmlspecialchars($row['target_url'] ?? '') ?></td>
            <td><?= ($row['status']==='active')?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>' ?></td>
            <td>
              <a href="?delete_gallery=<?= $row['id'] ?>&section=<?= $section ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar banner?')">
                <i class="bi bi-trash-fill"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$hasRows): ?>
          <tr><td colspan="4" class="text-center muted">No hay banners horizontales aún.</td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-center">
            <button type="button" class="btn btn-outline-primary btn-add-row" data-type="horizontal" data-section="<?= $section ?>">
              <i class="bi bi-plus-circle"></i> Agregar Banner Horizontal
            </button>
          </td>
        </tr>
      </tfoot>
    </table>

    <!-- Cuadrados -->
    <h5 class="mt-4">Banners Cuadrados</h5>
    <table class="table table-bordered align-middle" id="table-square-<?= $section ?>">
      <thead><tr><th>Imagen</th><th>URL</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php
        $hasRows=false;
        foreach($gallery as $row):
          if ($row['type']!=='square') continue; $hasRows=true; ?>
          <tr>
            <td><img src="<?= $row['image_url'] ?>" style="max-height:90px"></td>
            <td><?= htmlspecialchars($row['target_url'] ?? '') ?></td>
            <td><?= ($row['status']==='active')?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>' ?></td>
            <td>
              <a href="?delete_gallery=<?= $row['id'] ?>&section=<?= $section ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar banner?')">
                <i class="bi bi-trash-fill"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$hasRows): ?>
          <tr><td colspan="4" class="text-center muted">No hay banners cuadrados aún.</td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-center">
            <button type="button" class="btn btn-outline-primary btn-add-row" data-type="square" data-section="<?= $section ?>">
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
  <?php
  return ob_get_clean();
}
?>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>

<script>
// activar tab por hash (#gallery3, #gallery4, #gallery5, etc.)
document.addEventListener('DOMContentLoaded', function () {
  if (location.hash) {
    const trigger = document.querySelector(`a[href="${location.hash}"]`);
    if (trigger) new bootstrap.Tab(trigger).show();
  }
  // + Agregar fila dinámica
  document.querySelectorAll(".btn-add-row").forEach(btn=>{
    btn.addEventListener("click", function(){
      const type    = this.dataset.type;     // horizontal | square
      const section = this.dataset.section;  // 3 | 4 | 5
      const tbody   = document.querySelector(`#table-${type}-${section} tbody`);
      const row = document.createElement("tr");
      row.innerHTML = `
        <td><input type="file" name="image_${type}_${section}[]" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp" required></td>
        <td><input type="url"  name="url_${type}_${section}[]"   class="form-control"></td>
        <td>
          <select name="status_${type}_${section}[]" class="form-select">
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>
        </td>
        <td><button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bi bi-trash-fill"></i></button></td>
      `;
      tbody.appendChild(row);
      row.querySelector(".btn-remove-row").addEventListener("click", ()=> row.remove());
    });
  });
});
</script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if (!empty($_SESSION['flash'])):
  $flashes = $_SESSION['flash'];
  unset($_SESSION['flash']);
?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const queue = <?php echo json_encode($flashes, JSON_UNESCAPED_UNICODE); ?>;

  const iconMap = { success:'success', error:'error', warning:'warning', info:'info', question:'question' };
  (async () => {
    for (const f of queue) {
      const icon = iconMap[f.type] || 'info';
      await Swal.fire({
        icon: icon,
        title: f.msg,
        confirmButtonText: 'OK'
      });
    }
  })();
});
</script>
<?php endif; ?>
</body>
</html>








