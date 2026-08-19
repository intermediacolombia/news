<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';
$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/../login/restriction.php';
require_once __DIR__ . '/../inc/flash_helpers.php';

/* ══════════════════════════════════════════════════════════
   MODO API (AJAX) — responde JSON y termina
══════════════════════════════════════════════════════════ */
$action = $_POST['action'] ?? '';

if (in_array($action, ['start','batch','finish'], true)) {
    header('Content-Type: application/json');
    set_time_limit(120);
    ignore_user_abort(true);

    /* ── start: valida credenciales, guarda en sesión, retorna total ── */
    if ($action === 'start') {
        $wpHost   = trim($_POST['wp_host']   ?? 'localhost');
        $wpDb     = trim($_POST['wp_db']     ?? '');
        $wpUser   = trim($_POST['wp_user']   ?? '');
        $wpPass   = $_POST['wp_pass']        ?? '';
        $wpPrefix = trim($_POST['wp_prefix'] ?? 'wp_');
        $soloPublicados = !empty($_POST['solo_publicados']);
        $copiarImagenes = !empty($_POST['copiar_imagenes']);
        $wpUploadsPath  = trim($_POST['wp_uploads_path'] ?? '');

        if (!$wpDb) { echo json_encode(['ok'=>false,'error'=>'Nombre de BD requerido']); exit; }

        try {
            $wpPdo = new PDO(
                "mysql:host={$wpHost};dbname={$wpDb};charset=utf8mb4",
                $wpUser, $wpPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            echo json_encode(['ok'=>false,'error'=>'Conexión fallida: '.$e->getMessage()]); exit;
        }

        $statusWhere = $soloPublicados ? "AND p.post_status = 'publish'" : "AND p.post_status IN ('publish','draft','private')";
        $total = (int)$wpPdo->query("SELECT COUNT(*) FROM {$wpPrefix}posts p WHERE p.post_type='post' {$statusWhere}")->fetchColumn();

        /* ── Migrar categorías de una vez (son pocas) ── */
        $catMap = [];
        $cats_nuevas = 0;
        $stmtCats = $wpPdo->query("
            SELECT t.term_id, t.name, t.slug
            FROM {$wpPrefix}terms t
            INNER JOIN {$wpPrefix}term_taxonomy tt ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'category' AND t.slug != 'uncategorized'
            ORDER BY t.name
        ");
        foreach ($stmtCats->fetchAll() as $wc) {
            $chk = db()->prepare("SELECT id FROM blog_categories WHERE slug=? AND deleted=0 LIMIT 1");
            $chk->execute([$wc['slug']]);
            $existing = $chk->fetchColumn();
            if ($existing) {
                $catMap[$wc['term_id']] = (int)$existing;
            } else {
                db()->prepare("INSERT INTO blog_categories (name,slug,status,deleted) VALUES (?,?,'active',0)")
                    ->execute([$wc['name'], $wc['slug']]);
                $catMap[$wc['term_id']] = (int)db()->lastInsertId();
                $cats_nuevas++;
            }
        }

        /* Guardar contexto en sesión para los batches */
        $_SESSION['wp_migration'] = [
            'host'           => $wpHost,
            'db'             => $wpDb,
            'user'           => $wpUser,
            'pass'           => $wpPass,
            'prefix'         => $wpPrefix,
            'solo_publicados'=> $soloPublicados,
            'copiar_imagenes'=> $copiarImagenes,
            'uploads_path'   => $wpUploadsPath,
            'cat_map'        => $catMap,
            'total'          => $total,
            'migrados'       => 0,
            'existentes'     => 0,
            'imagenes'       => 0,
        ];

        echo json_encode(['ok'=>true,'total'=>$total,'cats_nuevas'=>$cats_nuevas]);
        exit;
    }

    /* ── batch: procesa BATCH_SIZE posts desde offset ── */
    if ($action === 'batch') {
        $ctx = $_SESSION['wp_migration'] ?? null;
        if (!$ctx) { echo json_encode(['ok'=>false,'error'=>'Sesión de migración no encontrada']); exit; }

        $offset     = (int)($_POST['offset'] ?? 0);
        $batchSize  = 50;

        try {
            $wpPdo = new PDO(
                "mysql:host={$ctx['host']};dbname={$ctx['db']};charset=utf8mb4",
                $ctx['user'], $ctx['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            echo json_encode(['ok'=>false,'error'=>'Conexión fallida: '.$e->getMessage()]); exit;
        }

        $prefix      = $ctx['prefix'];
        $catMap      = $ctx['cat_map'];
        $statusWhere = $ctx['solo_publicados'] ? "AND p.post_status='publish'" : "AND p.post_status IN ('publish','draft','private')";

        $stmt = $wpPdo->prepare("
            SELECT p.ID, p.post_title, p.post_name AS slug,
                   p.post_content, p.post_status,
                   p.post_date AS created_at, p.post_modified AS updated_at,
                   u.display_name AS author
            FROM {$prefix}posts p
            LEFT JOIN {$prefix}users u ON u.ID = p.post_author
            WHERE p.post_type='post' {$statusWhere}
            ORDER BY p.post_date ASC
            LIMIT {$batchSize} OFFSET {$offset}
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll();

        $migrados  = 0;
        $existentes = 0;
        $imagenes   = 0;
        $errors     = [];

        foreach ($posts as $wp) {
            try {
                $chkPost = db()->prepare("SELECT id FROM blog_posts WHERE slug=? AND deleted=0 LIMIT 1");
                $chkPost->execute([$wp['slug']]);
                if ($chkPost->fetchColumn()) { $existentes++; continue; }

                /* imagen destacada */
                $imagePath = null;
                $stmtThumb = $wpPdo->prepare("SELECT meta_value FROM {$prefix}postmeta WHERE post_id=? AND meta_key='_thumbnail_id' LIMIT 1");
                $stmtThumb->execute([$wp['ID']]);
                $thumbId = $stmtThumb->fetchColumn();

                if ($thumbId) {
                    $stmtFile = $wpPdo->prepare("SELECT meta_value FROM {$prefix}postmeta WHERE post_id=? AND meta_key='_wp_attached_file' LIMIT 1");
                    $stmtFile->execute([$thumbId]);
                    $wpFile = $stmtFile->fetchColumn();

                    if ($wpFile) {
                        $imagePath = 'public/images/blog/' . basename($wpFile);
                        if ($ctx['copiar_imagenes'] && $ctx['uploads_path']) {
                            $src  = rtrim($ctx['uploads_path'],'/\\') . '/' . $wpFile;
                            $dest = realpath(__DIR__.'/../../public/images').'/blog/'.basename($wpFile);
                            if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0755, true);
                            if (file_exists($src) && !file_exists($dest) && @copy($src, $dest)) {
                                $imagenes++;
                                try {
                                    $info = @getimagesize($dest);
                                    db()->prepare("INSERT IGNORE INTO multimedia (file_name,file_path,file_type,mime_type,file_size,width,height,uploaded_by,origin,origin_id) VALUES (?,?,'image',?,?,?,?,?,'wordpress',0)")
                                        ->execute([basename($wpFile),$imagePath,mime_content_type($dest),filesize($dest),$info[0]??null,$info[1]??null,$_SESSION['user']['id']]);
                                } catch (Throwable $e) {}
                            }
                        }
                    }
                }

                /* SEO */
                $seoTitle = $seoDesc = $seoKw = '';
                $stmtSeo = $wpPdo->prepare("SELECT meta_key,meta_value FROM {$prefix}postmeta WHERE post_id=? AND meta_key IN ('_yoast_wpseo_title','_yoast_wpseo_metadesc','rank_math_title','rank_math_description','rank_math_focus_keyword')");
                $stmtSeo->execute([$wp['ID']]);
                foreach ($stmtSeo->fetchAll() as $meta) {
                    if (in_array($meta['meta_key'],['_yoast_wpseo_title','rank_math_title'])) $seoTitle = $meta['meta_value'];
                    if (in_array($meta['meta_key'],['_yoast_wpseo_metadesc','rank_math_description'])) $seoDesc = $meta['meta_value'];
                    if ($meta['meta_key']==='rank_math_focus_keyword') $seoKw = $meta['meta_value'];
                }

                $status = $wp['post_status']==='publish' ? 'published' : 'draft';
                db()->prepare("INSERT INTO blog_posts (title,slug,content,image,author,author_user,status,seo_title,seo_description,seo_keywords,created_at,updated_at,deleted) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0)")
                    ->execute([$wp['post_title'],$wp['slug'],$wp['post_content'],$imagePath,$wp['author']??'WordPress','wordpress',$status,$seoTitle,$seoDesc,$seoKw,$wp['created_at'],$wp['updated_at']]);
                $postId = (int)db()->lastInsertId();

                /* categorías */
                $stmtTerms = $wpPdo->prepare("SELECT tt.term_id FROM {$prefix}term_relationships tr INNER JOIN {$prefix}term_taxonomy tt ON tt.term_taxonomy_id=tr.term_taxonomy_id WHERE tr.object_id=? AND tt.taxonomy='category'");
                $stmtTerms->execute([$wp['ID']]);
                foreach ($stmtTerms->fetchAll() as $term) {
                    if (isset($catMap[$term['term_id']])) {
                        db()->prepare("INSERT IGNORE INTO blog_post_category (post_id,category_id) VALUES (?,?)")
                            ->execute([$postId,$catMap[$term['term_id']]]);
                    }
                }
                $migrados++;
            } catch (Throwable $e) {
                $errors[] = "Post {$wp['slug']}: ".$e->getMessage();
            }
        }

        /* actualizar acumulados en sesión */
        $_SESSION['wp_migration']['migrados']  += $migrados;
        $_SESSION['wp_migration']['existentes'] += $existentes;
        $_SESSION['wp_migration']['imagenes']   += $imagenes;

        $done = count($posts) < $batchSize;
        echo json_encode([
            'ok'         => true,
            'migrados'   => $migrados,
            'existentes' => $existentes,
            'imagenes'   => $imagenes,
            'offset'     => $offset + count($posts),
            'done'       => $done,
            'errors'     => $errors,
        ]);
        exit;
    }

    /* ── finish: log y limpia sesión ── */
    if ($action === 'finish') {
        $ctx = $_SESSION['wp_migration'] ?? [];
        $resumen = "Posts migrados: {$ctx['migrados']}, existentes: {$ctx['existentes']}, imágenes: {$ctx['imagenes']}";
        log_system_action('migrate_wordpress', $resumen, 'blog_posts');
        unset($_SESSION['wp_migration']);
        echo json_encode(['ok'=>true,'resumen'=>$resumen]);
        exit;
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
    <style>
    .page-header {
      background: #fff; border-radius: 12px; padding: 1.2rem 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 1.5rem;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: .5rem;
    }
    .page-header h4 { margin: 0; font-weight: 700; color: #1e293b; }
    #progreso-wrap { display:none; }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../inc/menu.php'; ?>

<div class="page-wrapper">
  <div class="page-header">
    <h4><i class="fas fa-tools me-2" style="color:var(--primary-color)"></i>Herramientas</h4>
  </div>

<div class="wrap">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fab fa-wordpress me-2"></i> Migrar desde WordPress</h5>
            <span class="badge badge-brand">Herramientas</span>
        </div>
        <div class="card-body">

            <p class="text-muted mb-4">
                Importa posts, categorías e imágenes de una instalación de WordPress indicando
                las credenciales de su base de datos. Los posts ya existentes (mismo slug) se omiten
                automáticamente. La migración se realiza en lotes para evitar timeouts en sitios grandes.
            </p>

            <div id="resultado-wrap"></div>

            <!-- Progreso -->
            <div id="progreso-wrap" class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span id="progreso-label">Migrando…</span>
                    <span id="progreso-pct">0%</span>
                </div>
                <div class="progress" style="height:20px">
                    <div id="progreso-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                         style="width:0%"></div>
                </div>
                <small id="progreso-detalle" class="text-muted mt-1 d-block"></small>
            </div>

            <form id="form-migrar" autocomplete="off">

                <h6 class="fw-bold mt-2 mb-3 text-primary">
                    <i class="fa fa-database me-1"></i> Conexión a la BD de WordPress
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Host</label>
                        <input type="text" class="form-control" name="wp_host" value="localhost" placeholder="localhost">
                        <div class="hint mt-1">Por lo general <code>localhost</code></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre de la BD *</label>
                        <input type="text" class="form-control" name="wp_db" required placeholder="wordpress_db">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prefijo de tablas</label>
                        <input type="text" class="form-control" name="wp_prefix" value="wp_" placeholder="wp_">
                        <div class="hint mt-1">Por defecto es <code>wp_</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="wp_user" placeholder="root">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="wp_pass" placeholder="••••••••">
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3 text-primary"><i class="fa fa-image"></i> Imágenes</h6>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="copiar_imagenes" id="copiar_imagenes" value="1">
                            <label class="form-check-label" for="copiar_imagenes">Copiar imágenes destacadas al servidor local</label>
                        </div>
                        <div class="hint mt-1">Solo aplica si WordPress está en el mismo servidor o tienes acceso al sistema de archivos.</div>
                    </div>
                    <div class="col-12" id="uploads_path_row" style="display:none">
                        <label class="form-label">Ruta absoluta a <code>wp-content/uploads</code></label>
                        <input type="text" class="form-control" name="wp_uploads_path" placeholder="/var/www/html/wordpress/wp-content/uploads">
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3 text-primary"><i class="fa fa-filter me-1"></i> Filtros</h6>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="solo_publicados" id="solo_publicados" value="1" checked>
                        <label class="form-check-label" for="solo_publicados">Solo importar posts publicados (<code>publish</code>)</label>
                    </div>
                    <div class="hint mt-1">Si desactivas esta opción también se importarán borradores y privados.</div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button" id="btn-migrar" class="btn btn-success">
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

document.getElementById('btn-migrar').addEventListener('click', async function() {
    if (!confirm('¿Confirmas iniciar la migración? Los posts ya existentes se omitirán automáticamente.')) return;

    const form     = document.getElementById('form-migrar');
    const btn      = this;
    const wrap     = document.getElementById('progreso-wrap');
    const bar      = document.getElementById('progreso-bar');
    const pct      = document.getElementById('progreso-pct');
    const label    = document.getElementById('progreso-label');
    const detalle  = document.getElementById('progreso-detalle');
    const resWrap  = document.getElementById('resultado-wrap');
    const url      = window.location.href;

    resWrap.innerHTML = '';
    btn.disabled = true;

    /* ── start ── */
    const fd = new FormData(form);
    fd.append('action', 'start');
    let res;
    try {
        res = await fetch(url, {method:'POST', body:fd}).then(r => r.json());
    } catch(e) {
        resWrap.innerHTML = `<div class="alert alert-danger">Error de red: ${e.message}</div>`;
        btn.disabled = false;
        return;
    }
    if (!res.ok) {
        resWrap.innerHTML = `<div class="alert alert-danger">${res.error}</div>`;
        btn.disabled = false;
        return;
    }

    const total = res.total;
    wrap.style.display = '';
    label.textContent = `Migrando ${total} posts en lotes de 50…`;

    let offset = 0, totalMigrados = 0, totalExistentes = 0, totalImagenes = 0, allErrors = [];

    /* ── batches ── */
    while (true) {
        const bfd = new FormData();
        bfd.append('action', 'batch');
        bfd.append('offset', offset);

        let batch;
        try {
            batch = await fetch(url, {method:'POST', body:bfd}).then(r => r.json());
        } catch(e) {
            allErrors.push('Error de red en offset ' + offset + ': ' + e.message);
            break;
        }
        if (!batch.ok) { allErrors.push(batch.error); break; }

        totalMigrados   += batch.migrados;
        totalExistentes += batch.existentes;
        totalImagenes   += batch.imagenes;
        allErrors        = allErrors.concat(batch.errors || []);
        offset           = batch.offset;

        const p = total > 0 ? Math.round((offset / total) * 100) : 100;
        bar.style.width = p + '%';
        pct.textContent = p + '%';
        detalle.textContent = `Procesados: ${offset}/${total} — Migrados: ${totalMigrados}, Ya existían: ${totalExistentes}`;

        if (batch.done) break;
    }

    /* ── finish ── */
    await fetch(url, {method:'POST', body: (() => { const f=new FormData(); f.append('action','finish'); return f; })()}).then(r=>r.json()).catch(()=>{});

    bar.style.width = '100%';
    pct.textContent = '100%';
    bar.classList.remove('progress-bar-animated');
    label.textContent = 'Migración completada';

    let html = `<div class="alert alert-success">
        <strong><i class="fa fa-check-circle me-1"></i> Migración completada:</strong>
        <ul class="mb-0 mt-2">
            <li>Posts migrados: <strong>${totalMigrados}</strong></li>
            <li>Ya existían: <strong>${totalExistentes}</strong></li>
            <li>Imágenes copiadas: <strong>${totalImagenes}</strong></li>
            <li>Categorías nuevas: <strong>${res.cats_nuevas}</strong></li>
        </ul>
    </div>`;
    if (allErrors.length) {
        html += `<div class="alert alert-warning"><strong>Advertencias:</strong><ul class="mb-0 mt-2">${allErrors.map(e=>`<li>${e}</li>`).join('')}</ul></div>`;
    }
    resWrap.innerHTML = html;
    btn.disabled = false;
});
</script>
</body>
</html>
