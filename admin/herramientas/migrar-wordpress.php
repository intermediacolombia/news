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

    /* ── barra de progreso ── */
    .wp-mig-bar-wrap {
      background: #e9ecef; border-radius: 999px; height: 22px;
      overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,.08);
    }
    .wp-mig-bar {
      height: 100%; border-radius: 999px; position: relative; overflow: hidden;
      background: linear-gradient(90deg, var(--primary-color, #6366f1), #818cf8);
      transition: width .4s cubic-bezier(.4,0,.2,1);
      min-width: 0;
    }
    .wp-mig-bar-shine {
      position: absolute; top: 0; left: -60%; width: 40%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
      animation: shine 1.6s infinite;
    }
    @keyframes shine { to { left: 120%; } }

    /* ── spinner ── */
    .wp-mig-spinner {
      display: inline-block; width: 18px; height: 18px; border-radius: 50%;
      border: 3px solid #e2e8f0;
      border-top-color: var(--primary-color, #6366f1);
      animation: spin .7s linear infinite; flex-shrink: 0;
    }
    .wp-mig-spinner.done { animation: none; border-color: #22c55e; border-top-color: #22c55e; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── stat cards ── */
    .wp-stat-card {
      background: #fff; border: 1px solid #e9ecef; border-radius: 10px;
      padding: .7rem .8rem; text-align: center;
      box-shadow: 0 1px 4px rgba(0,0,0,.05);
      transition: transform .15s;
    }
    .wp-stat-card:hover { transform: translateY(-2px); }
    .wp-stat-icon { font-size: 1.3rem; margin-bottom: .2rem; }
    .wp-stat-val  { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
    .wp-stat-lbl  { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }

    /* ── log en vivo ── */
    .wp-mig-log {
      background: #0f172a; color: #94a3b8; border-radius: 8px;
      padding: .75rem 1rem; font-family: monospace; font-size: .78rem;
      max-height: 140px; overflow-y: auto; line-height: 1.6;
    }
    .wp-mig-log .log-ok   { color: #4ade80; }
    .wp-mig-log .log-skip { color: #fbbf24; }
    .wp-mig-log .log-err  { color: #f87171; }
    .wp-mig-log .log-info { color: #60a5fa; }
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
            <div id="progreso-wrap" class="mb-4" style="display:none">
                <div class="wp-mig-header d-flex align-items-center gap-2 mb-3">
                    <span class="wp-mig-spinner"></span>
                    <span id="progreso-label" class="fw-semibold">Preparando migración…</span>
                    <span id="progreso-pct" class="ms-auto badge" style="background:var(--primary-color);font-size:.95rem;min-width:52px;text-align:center">0%</span>
                </div>

                <div class="wp-mig-bar-wrap mb-3">
                    <div id="progreso-bar" class="wp-mig-bar" style="width:0%">
                        <span class="wp-mig-bar-shine"></span>
                    </div>
                </div>

                <!-- stats en tiempo real -->
                <div class="row g-2 mb-3" id="stats-row">
                    <div class="col-6 col-md-3">
                        <div class="wp-stat-card">
                            <div class="wp-stat-icon" style="color:#22c55e"><i class="fas fa-check-circle"></i></div>
                            <div class="wp-stat-val" id="stat-migrados">0</div>
                            <div class="wp-stat-lbl">Migrados</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="wp-stat-card">
                            <div class="wp-stat-icon" style="color:#f59e0b"><i class="fas fa-copy"></i></div>
                            <div class="wp-stat-val" id="stat-existentes">0</div>
                            <div class="wp-stat-lbl">Ya existían</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="wp-stat-card">
                            <div class="wp-stat-icon" style="color:#3b82f6"><i class="fas fa-image"></i></div>
                            <div class="wp-stat-val" id="stat-imagenes">0</div>
                            <div class="wp-stat-lbl">Imágenes</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="wp-stat-card">
                            <div class="wp-stat-icon" style="color:#8b5cf6"><i class="fas fa-layer-group"></i></div>
                            <div class="wp-stat-val" id="stat-lote">0</div>
                            <div class="wp-stat-lbl">Lote actual</div>
                        </div>
                    </div>
                </div>

                <!-- log en vivo -->
                <div id="mig-log" class="wp-mig-log"></div>
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

    const form    = document.getElementById('form-migrar');
    const btn     = this;
    const wrap    = document.getElementById('progreso-wrap');
    const bar     = document.getElementById('progreso-bar');
    const pct     = document.getElementById('progreso-pct');
    const label   = document.getElementById('progreso-label');
    const spinner = wrap.querySelector('.wp-mig-spinner');
    const resWrap = document.getElementById('resultado-wrap');
    const log     = document.getElementById('mig-log');
    const url     = window.location.href;

    const setCounter = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val.toLocaleString();
    };
    const addLog = (msg, cls = '') => {
        const line = document.createElement('div');
        line.className = cls;
        line.textContent = msg;
        log.appendChild(line);
        log.scrollTop = log.scrollHeight;
    };

    resWrap.innerHTML = '';
    log.innerHTML = '';
    btn.disabled = true;
    wrap.style.display = '';

    addLog('⟳ Conectando a la base de datos de WordPress…', 'log-info');

    /* ── start ── */
    const fd = new FormData(form);
    fd.append('action', 'start');
    let res;
    try {
        res = await fetch(url, {method:'POST', body:fd}).then(r => r.json());
    } catch(e) {
        resWrap.innerHTML = `<div class="alert alert-danger">Error de red: ${e.message}</div>`;
        wrap.style.display = 'none';
        btn.disabled = false;
        return;
    }
    if (!res.ok) {
        resWrap.innerHTML = `<div class="alert alert-danger">${res.error}</div>`;
        wrap.style.display = 'none';
        btn.disabled = false;
        return;
    }

    const total = res.total;
    addLog(`✔ Conexión OK — ${res.cats_nuevas} categorías nuevas importadas.`, 'log-ok');
    addLog(`⟳ Iniciando migración de ${total.toLocaleString()} posts en lotes de 50…`, 'log-info');
    label.textContent = `Migrando ${total.toLocaleString()} posts…`;
    setCounter('stat-lote', 1);

    let offset = 0, totalMigrados = 0, totalExistentes = 0, totalImagenes = 0, allErrors = [], lote = 1;

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
            addLog('✖ ' + e.message, 'log-err');
            break;
        }
        if (!batch.ok) { allErrors.push(batch.error); addLog('✖ ' + batch.error, 'log-err'); break; }

        totalMigrados   += batch.migrados;
        totalExistentes += batch.existentes;
        totalImagenes   += batch.imagenes;
        (batch.errors || []).forEach(e => { allErrors.push(e); addLog('⚠ ' + e, 'log-err'); });
        offset = batch.offset;

        const p = total > 0 ? Math.min(Math.round((offset / total) * 100), 99) : 99;
        bar.style.width = p + '%';
        pct.textContent = p + '%';
        setCounter('stat-migrados',   totalMigrados);
        setCounter('stat-existentes', totalExistentes);
        setCounter('stat-imagenes',   totalImagenes);
        setCounter('stat-lote',       ++lote);

        if (batch.migrados > 0)
            addLog(`✔ Lote ${lote}: ${batch.migrados} migrados, ${batch.existentes} omitidos${batch.imagenes ? ', '+batch.imagenes+' imgs' : ''}`, 'log-ok');
        else
            addLog(`↷ Lote ${lote}: ${batch.existentes} ya existían, se omitieron`, 'log-skip');

        if (batch.done) break;
    }

    /* ── finish ── */
    const ff = new FormData(); ff.append('action','finish');
    await fetch(url, {method:'POST', body:ff}).then(r=>r.json()).catch(()=>{});

    bar.style.width = '100%';
    pct.textContent = '100%';
    spinner.classList.add('done');
    label.textContent = 'Migración completada';
    addLog(`✔ Listo — ${totalMigrados} posts migrados, ${totalExistentes} omitidos, ${totalImagenes} imágenes.`, 'log-ok');

    let html = `<div class="alert alert-success mt-3">
        <strong><i class="fa fa-check-circle me-1"></i> Migración completada</strong>
        <div class="row g-2 mt-2">
            <div class="col-6 col-md-3"><strong>${totalMigrados.toLocaleString()}</strong><br><small>Posts migrados</small></div>
            <div class="col-6 col-md-3"><strong>${totalExistentes.toLocaleString()}</strong><br><small>Ya existían</small></div>
            <div class="col-6 col-md-3"><strong>${totalImagenes.toLocaleString()}</strong><br><small>Imágenes</small></div>
            <div class="col-6 col-md-3"><strong>${res.cats_nuevas}</strong><br><small>Categorías nuevas</small></div>
        </div>
    </div>`;
    if (allErrors.length) {
        html += `<div class="alert alert-warning"><strong>Advertencias (${allErrors.length}):</strong><ul class="mb-0 mt-2">${allErrors.map(e=>`<li>${e}</li>`).join('')}</ul></div>`;
    }
    resWrap.innerHTML = html;
    btn.disabled = false;
});
</script>
</body>
</html>
