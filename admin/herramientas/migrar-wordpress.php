<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

$resultado  = [];
$errores    = [];
$migrado    = false;

/* ══════════════════════════════════════════════════════════
   PROCESAR MIGRACIÓN
══════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrar'])) {

    $wpHost   = trim($_POST['wp_host']   ?? 'localhost');
    $wpDb     = trim($_POST['wp_db']     ?? '');
    $wpUser   = trim($_POST['wp_user']   ?? '');
    $wpPass   = $_POST['wp_pass']        ?? '';
    $wpPrefix = trim($_POST['wp_prefix'] ?? 'wp_');
    $copiarImagenes = !empty($_POST['copiar_imagenes']);
    $soloPublicados = !empty($_POST['solo_publicados']);

    if (!$wpDb) {
        $errores[] = "El nombre de la base de datos de WordPress es obligatorio.";
    }

    if (empty($errores)) {
        /* ── Conectar a BD de WordPress ── */
        try {
            $wpPdo = new PDO(
                "mysql:host={$wpHost};dbname={$wpDb};charset=utf8mb4",
                $wpUser, $wpPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            $errores[] = "No se pudo conectar a la BD de WordPress: " . $e->getMessage();
        }
    }

    if (empty($errores)) {

        $statusWhere = $soloPublicados ? "AND p.post_status = 'publish'" : "AND p.post_status IN ('publish','draft','private')";

        /* ══════════════════════════════
           1. MIGRAR CATEGORÍAS
        ══════════════════════════════ */
        $catMap = []; // wp_term_id => local_id
        $cats_nuevas = 0;
        $cats_existentes = 0;

        try {
            $stmtCats = $wpPdo->query("
                SELECT t.term_id, t.name, t.slug
                FROM {$wpPrefix}terms t
                INNER JOIN {$wpPrefix}term_taxonomy tt ON tt.term_id = t.term_id
                WHERE tt.taxonomy = 'category'
                  AND t.slug != 'uncategorized'
                ORDER BY t.name
            ");
            $wpCats = $stmtCats->fetchAll();

            foreach ($wpCats as $wc) {
                // ¿Ya existe por slug?
                $chk = db()->prepare("SELECT id FROM blog_categories WHERE slug=? AND deleted=0 LIMIT 1");
                $chk->execute([$wc['slug']]);
                $existing = $chk->fetchColumn();

                if ($existing) {
                    $catMap[$wc['term_id']] = (int)$existing;
                    $cats_existentes++;
                } else {
                    db()->prepare("INSERT INTO blog_categories (name, slug, status, deleted) VALUES (?,?,'active',0)")
                        ->execute([$wc['name'], $wc['slug']]);
                    $newId = (int)db()->lastInsertId();
                    $catMap[$wc['term_id']] = $newId;
                    $cats_nuevas++;
                }
            }
            $resultado[] = "Categorías: {$cats_nuevas} nuevas, {$cats_existentes} ya existían.";
        } catch (Throwable $e) {
            $errores[] = "Error al migrar categorías: " . $e->getMessage();
        }

        /* ══════════════════════════════
           2. MIGRAR POSTS
        ══════════════════════════════ */
        if (empty($errores)) {
            $posts_nuevos    = 0;
            $posts_existentes = 0;
            $imagenes_copiadas = 0;

            try {
                $stmtPosts = $wpPdo->query("
                    SELECT p.ID, p.post_title, p.post_name AS slug,
                           p.post_content, p.post_status,
                           p.post_date AS created_at,
                           p.post_modified AS updated_at,
                           u.display_name AS author
                    FROM {$wpPrefix}posts p
                    LEFT JOIN {$wpPrefix}users u ON u.ID = p.post_author
                    WHERE p.post_type = 'post'
                      {$statusWhere}
                    ORDER BY p.post_date ASC
                ");
                $wpPosts = $stmtPosts->fetchAll();

                foreach ($wpPosts as $wp) {

                    /* ── Verificar si ya fue migrado (por slug) ── */
                    $chkPost = db()->prepare("SELECT id FROM blog_posts WHERE slug=? AND deleted=0 LIMIT 1");
                    $chkPost->execute([$wp['slug']]);
                    if ($chkPost->fetchColumn()) {
                        $posts_existentes++;
                        continue;
                    }

                    /* ── Imagen destacada ── */
                    $imagePath = null;
                    $thumbId = null;

                    $stmtThumb = $wpPdo->prepare("
                        SELECT meta_value FROM {$wpPrefix}postmeta
                        WHERE post_id=? AND meta_key='_thumbnail_id' LIMIT 1
                    ");
                    $stmtThumb->execute([$wp['ID']]);
                    $thumbId = $stmtThumb->fetchColumn();

                    if ($thumbId) {
                        $stmtFile = $wpPdo->prepare("
                            SELECT meta_value FROM {$wpPrefix}postmeta
                            WHERE post_id=? AND meta_key='_wp_attached_file' LIMIT 1
                        ");
                        $stmtFile->execute([$thumbId]);
                        $wpFile = $stmtFile->fetchColumn();

                        if ($wpFile) {
                            $imagePath = 'public/images/blog/' . basename($wpFile);

                            if ($copiarImagenes) {
                                /* Intentar copiar desde wp-content/uploads */
                                $wpUploadsDir = isset($_POST['wp_uploads_path'])
                                    ? rtrim(trim($_POST['wp_uploads_path']), '/\\')
                                    : null;

                                if ($wpUploadsDir && file_exists($wpUploadsDir . '/' . $wpFile)) {
                                    $destDir = realpath(__DIR__ . '/../../public/images') . '/blog/';
                                    if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
                                    $dest = $destDir . basename($wpFile);
                                    if (!file_exists($dest) && @copy($wpUploadsDir . '/' . $wpFile, $dest)) {
                                        $imagenes_copiadas++;
                                        /* Registrar en multimedia */
                                        try {
                                            $info = @getimagesize($dest);
                                            db()->prepare("INSERT IGNORE INTO multimedia
                                                (file_name, file_path, file_type, mime_type, file_size, width, height, uploaded_by, origin, origin_id)
                                                VALUES (?,?,'image',?,?,?,?,?,'wordpress',0)")
                                                ->execute([
                                                    basename($wpFile),
                                                    $imagePath,
                                                    mime_content_type($dest),
                                                    filesize($dest),
                                                    $info[0] ?? null,
                                                    $info[1] ?? null,
                                                    $_SESSION['user']['id'],
                                                ]);
                                        } catch (Throwable $e) {}
                                    }
                                }
                            }
                        }
                    }

                    /* ── Status mapping ── */
                    $status = ($wp['post_status'] === 'publish') ? 'published' : 'draft';

                    /* ── SEO: intentar yoast o rank math ── */
                    $seoTitle = $seoDesc = $seoKw = '';
                    try {
                        $stmtSeo = $wpPdo->prepare("
                            SELECT meta_key, meta_value FROM {$wpPrefix}postmeta
                            WHERE post_id=? AND meta_key IN (
                                '_yoast_wpseo_title','_yoast_wpseo_metadesc',
                                'rank_math_title','rank_math_description','rank_math_focus_keyword'
                            )
                        ");
                        $stmtSeo->execute([$wp['ID']]);
                        foreach ($stmtSeo->fetchAll() as $meta) {
                            if (in_array($meta['meta_key'], ['_yoast_wpseo_title','rank_math_title']))
                                $seoTitle = $meta['meta_value'];
                            if (in_array($meta['meta_key'], ['_yoast_wpseo_metadesc','rank_math_description']))
                                $seoDesc = $meta['meta_value'];
                            if ($meta['meta_key'] === 'rank_math_focus_keyword')
                                $seoKw = $meta['meta_value'];
                        }
                    } catch (Throwable $e) {}

                    /* ── Insertar post ── */
                    db()->prepare("
                        INSERT INTO blog_posts
                            (title, slug, content, image, author, author_user, status,
                             seo_title, seo_description, seo_keywords,
                             created_at, updated_at, deleted)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0)
                    ")->execute([
                        $wp['post_title'],
                        $wp['slug'],
                        $wp['post_content'],
                        $imagePath,
                        $wp['author'] ?? 'WordPress',
                        'wordpress',
                        $status,
                        $seoTitle,
                        $seoDesc,
                        $seoKw,
                        $wp['created_at'],
                        $wp['updated_at'],
                    ]);
                    $postId = (int)db()->lastInsertId();

                    /* ── Asignar categorías ── */
                    try {
                        $stmtTerms = $wpPdo->prepare("
                            SELECT tt.term_id
                            FROM {$wpPrefix}term_relationships tr
                            INNER JOIN {$wpPrefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                            WHERE tr.object_id=? AND tt.taxonomy='category'
                        ");
                        $stmtTerms->execute([$wp['ID']]);
                        foreach ($stmtTerms->fetchAll() as $term) {
                            if (isset($catMap[$term['term_id']])) {
                                db()->prepare("INSERT IGNORE INTO blog_post_category (post_id, category_id) VALUES (?,?)")
                                    ->execute([$postId, $catMap[$term['term_id']]]);
                            }
                        }
                    } catch (Throwable $e) {}

                    $posts_nuevos++;
                }

                $resultado[] = "Posts: {$posts_nuevos} migrados, {$posts_existentes} ya existían.";
                if ($copiarImagenes) {
                    $resultado[] = "Imágenes copiadas: {$imagenes_copiadas}.";
                }
                $migrado = true;
                log_system_action('migrate_wordpress', 'Migró desde WordPress: ' . implode(', ', $resultado), 'blog_posts');

            } catch (Throwable $e) {
                $errores[] = "Error al migrar posts: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Migrar desde WordPress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/../inc/header.php'; ?>
</head>
<body>
<div class="container" style="padding:0; background:rgba(0,0,0,0)">
    <div class="portada">
        <h1 class="mb-4"><i class="fa fa-screwdriver-wrench"></i> Herramientas</h1>
    </div>
</div>
<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="wrap">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fa fa-wordpress me-2"></i> Migrar desde WordPress</h5>
            <span class="badge badge-brand">Herramientas</span>
        </div>
        <div class="card-body">

            <p class="text-muted mb-4">
                Importa posts, categorías e imágenes de una instalación de WordPress indicando
                las credenciales de su base de datos. Los posts ya existentes (mismo slug) se omiten
                automáticamente, por lo que puedes ejecutar la migración varias veces de forma segura.
            </p>

            <?php if ($migrado): ?>
            <div class="alert alert-success">
                <strong><i class="fa fa-check-circle me-1"></i> Migración completada:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($resultado as $r): ?>
                    <li><?= htmlspecialchars($r) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <strong><i class="fa fa-times-circle me-1"></i> Errores:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">

                <h6 class="fw-bold mt-2 mb-3 text-primary">
                    <i class="fa fa-database me-1"></i> Conexión a la BD de WordPress
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Host</label>
                        <input type="text" class="form-control" name="wp_host"
                               value="<?= htmlspecialchars($_POST['wp_host'] ?? 'localhost') ?>"
                               placeholder="localhost">
                        <div class="hint mt-1">Por lo general <code>localhost</code></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre de la BD *</label>
                        <input type="text" class="form-control" name="wp_db" required
                               value="<?= htmlspecialchars($_POST['wp_db'] ?? '') ?>"
                               placeholder="wordpress_db">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prefijo de tablas</label>
                        <input type="text" class="form-control" name="wp_prefix"
                               value="<?= htmlspecialchars($_POST['wp_prefix'] ?? 'wp_') ?>"
                               placeholder="wp_">
                        <div class="hint mt-1">Por defecto es <code>wp_</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="wp_user"
                               value="<?= htmlspecialchars($_POST['wp_user'] ?? '') ?>"
                               placeholder="root">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="wp_pass"
                               placeholder="••••••••">
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3 text-primary">
                    <i class="fa fa-image"></i> Imágenes
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="copiar_imagenes"
                                   id="copiar_imagenes" value="1"
                                   <?= !empty($_POST['copiar_imagenes']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="copiar_imagenes">
                                Copiar imágenes destacadas al servidor local
                            </label>
                        </div>
                        <div class="hint mt-1">
                            Solo aplica si WordPress está en el mismo servidor o tienes acceso al sistema de archivos.
                        </div>
                    </div>
                    <div class="col-12" id="uploads_path_row" style="<?= empty($_POST['copiar_imagenes']) ? 'display:none' : '' ?>">
                        <label class="form-label">Ruta absoluta a <code>wp-content/uploads</code></label>
                        <input type="text" class="form-control" name="wp_uploads_path"
                               value="<?= htmlspecialchars($_POST['wp_uploads_path'] ?? '') ?>"
                               placeholder="/var/www/html/wordpress/wp-content/uploads">
                        <div class="hint mt-1">
                            Ejemplo: <code>/var/www/html/mi-wordpress/wp-content/uploads</code>
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3 text-primary">
                    <i class="fa fa-filter me-1"></i> Filtros
                </h6>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="solo_publicados"
                               id="solo_publicados" value="1"
                               <?= !isset($_POST['migrar']) || !empty($_POST['solo_publicados']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="solo_publicados">
                            Solo importar posts publicados (<code>publish</code>)
                        </label>
                    </div>
                    <div class="hint mt-1">Si desactivas esta opción también se importarán borradores y privados.</div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" name="migrar" value="1" class="btn btn-success"
                            onclick="return confirm('¿Confirmas iniciar la migración? Los posts ya existentes se omitirán automáticamente.')">
                        <i class="fa fa-play me-1"></i> Iniciar migración
                    </button>
                    <a href="<?= URLBASE ?>/admin/" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
<script>
document.getElementById('copiar_imagenes').addEventListener('change', function(){
    document.getElementById('uploads_path_row').style.display = this.checked ? '' : 'none';
});
</script>
</body>
</html>
