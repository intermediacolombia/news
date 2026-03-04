<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

header('Content-Type: application/json');

// Capturar errores PHP y devolverlos como JSON válido
set_error_handler(function($errno, $errstr) {
    echo json_encode([
        'draw'            => 1,
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => $errstr,
    ]);
    exit;
});

try {
    $canViewOthers = isset($_SESSION['user_permissions'])
                     && in_array('Ver Otras Entradas', $_SESSION['user_permissions']);
    $currentUser   = $_SESSION['user']['username'] ?? $_SESSION['user']['correo'] ?? null;

    $draw        = intval($_POST['draw']                ?? 1);
    $start       = intval($_POST['start']               ?? 0);
    $length      = intval($_POST['length']              ?? 25);
    $searchValue = trim($_POST['search']['value']       ?? '');
    $orderColumn = intval($_POST['order'][0]['column']  ?? 6);
    $orderDir    = $_POST['order'][0]['dir']            ?? 'desc';
    $orderDir    = $orderDir === 'asc' ? 'asc' : 'desc';

    // Columnas ordenables
    $columns = ['', '', 'p.title', '', 'p.author', 'p.status', 'p.created_at'];
    $orderBy = $columns[$orderColumn] ?? 'p.created_at';
    if (empty($orderBy)) $orderBy = 'p.created_at';

    // ── WHERE base ──
    $whereConditions = ["p.deleted = 0"];
    $params          = [];

    if (!$canViewOthers) {
        $whereConditions[] = "p.author_user = :current_user";
        $params[':current_user'] = $currentUser;
    }

    if (!empty($searchValue)) {
        $whereConditions[] = "(p.title LIKE :search OR p.author LIKE :search2)";
        $params[':search']  = "%$searchValue%";
        $params[':search2'] = "%$searchValue%";
    }

    $whereSQL = implode(' AND ', $whereConditions);

    // ── Total sin filtro ──
    $sqlTotal = "SELECT COUNT(DISTINCT p.id) FROM blog_posts p WHERE p.deleted = 0";
    if (!$canViewOthers) {
        $sqlTotal .= " AND p.author_user = :current_user";
        $stTotal = db()->prepare($sqlTotal);
        $stTotal->execute([':current_user' => $currentUser]);
    } else {
        $stTotal = db()->prepare($sqlTotal);
        $stTotal->execute();
    }
    $recordsTotal = (int)$stTotal->fetchColumn();

    // ── Total filtrado ──
    $sqlFiltered = "SELECT COUNT(DISTINCT p.id) FROM blog_posts p WHERE $whereSQL";
    $stFiltered  = db()->prepare($sqlFiltered);
    $stFiltered->execute($params);
    $recordsFiltered = (int)$stFiltered->fetchColumn();

    // ── Query principal ──
    // Subconsulta para categorías evita conflicto con GROUP BY
    $sql = "
        SELECT 
            p.id,
            p.title,
            p.image,
            p.author,
            p.author_user,
            p.status,
            p.created_at,
            (
                SELECT GROUP_CONCAT(DISTINCT c2.name SEPARATOR ', ')
                FROM blog_post_category pc2
                JOIN blog_categories c2 ON c2.id = pc2.category_id AND c2.deleted = 0
                WHERE pc2.post_id = p.id
            ) AS categorias,
            u.foto_perfil AS author_foto
        FROM blog_posts p
        LEFT JOIN usuarios u ON u.username = p.author_user AND u.borrado = 0
        WHERE $whereSQL
        ORDER BY $orderBy $orderDir
        LIMIT :limit_start, :limit_length
    ";

    $st = db()->prepare($sql);

    // Bind params nombrados
    foreach ($params as $key => $value) {
        $st->bindValue($key, $value);
    }
    $st->bindValue(':limit_start',  $start,  PDO::PARAM_INT);
    $st->bindValue(':limit_length', $length, PDO::PARAM_INT);
    $st->execute();
    $posts = $st->fetchAll(PDO::FETCH_ASSOC);

    // ── Formatear ──
    $data = [];
    foreach ($posts as $p) {

        // Imagen del post
        $image = !empty($p['image'])
            ? '<img src="' . htmlspecialchars($GLOBALS['url'] . '/' . $p['image']) . '" class="post-thumb">'
            : '<span class="text-muted small">Sin imagen</span>';

        // Título
        $titleFull    = htmlspecialchars($p['title']);
        $titleDisplay = mb_strlen($p['title']) > 60
            ? htmlspecialchars(mb_substr($p['title'], 0, 60)) . '…'
            : $titleFull;
        $titleHtml = '<div class="post-title" title="' . $titleFull . '"><strong>' . $titleDisplay . '</strong></div>';

        // Autor con foto
        $fotoHtml = '';
        if (!empty($p['author_foto'])) {
            $fotoHtml = '<img src="' . htmlspecialchars($GLOBALS['url'] . '/' . $p['author_foto']) . '"
                              style="width:24px;height:24px;border-radius:50%;object-fit:cover;margin-right:6px;vertical-align:middle;">';
        }
        $authorHtml = $fotoHtml . htmlspecialchars($p['author']);

        // Badge estado
        $badge = $p['status'] === 'published'
            ? '<span class="badge bg-success">Publicado</span>'
            : '<span class="badge bg-secondary">Borrador</span>';

        // Acciones
        $fotoData = !empty($p['author_foto']) ? htmlspecialchars($p['author_foto']) : '';
        $actions  = '
            <a class="btn btn-sm btn-outline-primary" 
               href="' . $GLOBALS['url'] . '/admin/blog/edit.php?id=' . (int)$p['id'] . '" 
               title="Editar">
                <i class="fa fa-pencil"></i>
            </a>
            <button class="btn-transfer-row btn-transfer-single"
                    data-id="' . (int)$p['id'] . '"
                    data-author="' . htmlspecialchars($p['author']) . '"
                    data-foto="' . $fotoData . '"
                    title="Transferir autoría">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </button>
            <button class="btn-trash btn-delete"
                    data-id="' . (int)$p['id'] . '"
                    data-name="' . htmlspecialchars($p['title']) . '"
                    title="Eliminar">
                <i class="fa fa-trash"></i>
            </button>
        ';

        $data[] = [
            '<input type="checkbox" class="chkPost form-check-input" value="' . (int)$p['id'] . '">',
            $image,
            $titleHtml,
            htmlspecialchars($p['categorias'] ?: '—'),
            $authorHtml,
            $badge,
            $p['created_at'],
            $actions,
        ];
    }

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data,
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'draw'            => intval($_POST['draw'] ?? 1),
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => 'Error: ' . $e->getMessage(),
    ]);
}