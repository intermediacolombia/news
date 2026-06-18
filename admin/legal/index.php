<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Gestionar Páginas Legales';
include('../login/restriction.php');
require_once __DIR__ . '/../inc/flash_helpers.php';

/* ── Slugs permitidos ─────────────────────────────────────────────────── */
$allowed_slugs = ['aviso-legal', 'politica-privacidad'];

/* ── Manejo del POST ──────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $slug    = trim($_POST['slug'] ?? '');
    $title   = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';

    if (!in_array($slug, $allowed_slugs, true)) {
        setFlash('error', 'Slug no válido.');
        header('Location: ' . URLBASE . '/admin/legal/?tab=' . urlencode($slug));
        exit;
    }

    if ($title === '') {
        setFlash('error', 'El título no puede estar vacío.');
        header('Location: ' . URLBASE . '/admin/legal/?tab=' . urlencode($slug));
        exit;
    }

    try {
        $stmt = db()->prepare("UPDATE legal_pages SET title = ?, content = ? WHERE slug = ?");
        $stmt->execute([$title, $content, $slug]);

        $row = db()->prepare("SELECT id FROM legal_pages WHERE slug = ?");
        $row->execute([$slug]);
        $page_id = (int)($row->fetchColumn() ?: 0);

        log_system_action('Guardar Página Legal', $slug, 'legal_pages', $page_id);
        setFlash('success', 'Página legal guardada correctamente.');
    } catch (Throwable $e) {
        setFlash('error', 'Error al guardar: ' . $e->getMessage());
    }

    header('Location: ' . URLBASE . '/admin/legal/?tab=' . urlencode($slug));
    exit;
}

/* ── Cargar ambas páginas desde la BD ────────────────────────────────── */
$pages = [];
try {
    $stmt = db()->query("SELECT * FROM legal_pages WHERE slug IN ('aviso-legal','politica-privacidad')");
    foreach ($stmt->fetchAll() as $row) {
        $pages[$row['slug']] = $row;
    }
} catch (Throwable $e) {
    $pages = [];
}

/* ── Tab activo ───────────────────────────────────────────────────────── */
$active_tab = $_GET['tab'] ?? 'aviso-legal';
if (!in_array($active_tab, $allowed_slugs, true)) {
    $active_tab = 'aviso-legal';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Páginas Legales</title>
<?php include('../inc/header.php'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css">
<style>
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
  .nav-tabs .nav-link.active {
    font-weight: 600;
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
  }
  .updated-at {
    font-size: 0.82rem;
    color: #6c757d;
    margin-top: .5rem;
  }
</style>
</head>
<body>
<?php include('../inc/menu.php'); ?>

<div class="page-wrapper">

  <!-- Page header -->
  <div class="page-header">
    <h4><i class="fas fa-file-contract me-2" style="color:var(--primary-color)"></i>Páginas Legales</h4>
  </div>

  <!-- Bootstrap Tabs -->
  <div class="card shadow-sm">
    <div class="card-body">

      <ul class="nav nav-tabs mb-4" id="legalTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link<?= $active_tab === 'aviso-legal' ? ' active' : '' ?>"
                  id="tab-aviso-legal"
                  data-bs-toggle="tab"
                  data-bs-target="#panel-aviso-legal"
                  type="button" role="tab"
                  aria-controls="panel-aviso-legal"
                  aria-selected="<?= $active_tab === 'aviso-legal' ? 'true' : 'false' ?>">
            <i class="fas fa-gavel me-1"></i> Aviso Legal
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link<?= $active_tab === 'politica-privacidad' ? ' active' : '' ?>"
                  id="tab-politica-privacidad"
                  data-bs-toggle="tab"
                  data-bs-target="#panel-politica-privacidad"
                  type="button" role="tab"
                  aria-controls="panel-politica-privacidad"
                  aria-selected="<?= $active_tab === 'politica-privacidad' ? 'true' : 'false' ?>">
            <i class="fas fa-shield-alt me-1"></i> Política de Privacidad
          </button>
        </li>
      </ul>

      <div class="tab-content" id="legalTabsContent">

        <!-- ── Aviso Legal ─────────────────────────────────────────── -->
        <div class="tab-pane fade<?= $active_tab === 'aviso-legal' ? ' show active' : '' ?>"
             id="panel-aviso-legal"
             role="tabpanel"
             aria-labelledby="tab-aviso-legal">

          <form method="POST" action="">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="slug"   value="aviso-legal">

            <div class="mb-3">
              <label for="title_aviso" class="form-label fw-semibold">Título</label>
              <input type="text"
                     id="title_aviso"
                     name="title"
                     class="form-control"
                     value="<?= htmlspecialchars($pages['aviso-legal']['title'] ?? '') ?>"
                     required>
            </div>

            <div class="mb-3">
              <label for="content_aviso" class="form-label fw-semibold">Contenido</label>
              <textarea id="content_aviso" name="content"><?= $pages['aviso-legal']['content'] ?? '' ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i> Guardar Aviso Legal
            </button>
          </form>

          <?php if (!empty($pages['aviso-legal']['updated_at'])): ?>
            <p class="updated-at mt-3">
              <i class="fas fa-clock me-1"></i>
              Última actualización: <?= htmlspecialchars($pages['aviso-legal']['updated_at']) ?>
            </p>
          <?php endif; ?>
        </div>

        <!-- ── Política de Privacidad ─────────────────────────────── -->
        <div class="tab-pane fade<?= $active_tab === 'politica-privacidad' ? ' show active' : '' ?>"
             id="panel-politica-privacidad"
             role="tabpanel"
             aria-labelledby="tab-politica-privacidad">

          <form method="POST" action="">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="slug"   value="politica-privacidad">

            <div class="mb-3">
              <label for="title_privacidad" class="form-label fw-semibold">Título</label>
              <input type="text"
                     id="title_privacidad"
                     name="title"
                     class="form-control"
                     value="<?= htmlspecialchars($pages['politica-privacidad']['title'] ?? '') ?>"
                     required>
            </div>

            <div class="mb-3">
              <label for="content_privacidad" class="form-label fw-semibold">Contenido</label>
              <textarea id="content_privacidad" name="content"><?= $pages['politica-privacidad']['content'] ?? '' ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i> Guardar Política de Privacidad
            </button>
          </form>

          <?php if (!empty($pages['politica-privacidad']['updated_at'])): ?>
            <p class="updated-at mt-3">
              <i class="fas fa-clock me-1"></i>
              Última actualización: <?= htmlspecialchars($pages['politica-privacidad']['updated_at']) ?>
            </p>
          <?php endif; ?>
        </div>

      </div><!-- /.tab-content -->
    </div><!-- /.card-body -->
  </div><!-- /.card -->

</div><!-- /.page-wrapper -->

<?php include('../inc/menu-footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<?php include('../inc/flash_simple.php'); ?>

<script>
$(document).ready(function () {

  var toolbarConfig = [
    ['style', ['bold', 'italic', 'underline']],
    ['para',  ['ul', 'ol']],
    ['insert',['link', 'hr']]
  ];

  $('#content_aviso').summernote({
    toolbar: toolbarConfig,
    height: 320,
    lang: 'es-ES',
    callbacks: {
      onInit: function () {
        // ensure Summernote doesn't strip existing HTML
      }
    }
  });

  $('#content_privacidad').summernote({
    toolbar: toolbarConfig,
    height: 320,
    lang: 'es-ES'
  });

  // Activate the correct tab based on PHP-set active class (already handled server-side).
  // Also update URL hash when switching tabs so back-button works.
  $('#legalTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    var slug = $(e.target).attr('id').replace('tab-', '');
    history.replaceState(null, '', '?tab=' + slug);
  });

});
</script>
</body>
</html>
