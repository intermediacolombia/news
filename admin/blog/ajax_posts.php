<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';


header('Content-Type: application/json');

// Verificar permisos
$canViewOthers = isset($_SESSION['user_permissions']) && in_array('Ver Otras Entradas', $_SESSION['user_permissions']);
$currentUser = $_SESSION['user']['username'] ?? $_SESSION['user']['correo'] ?? null;

// Parámetros de DataTables
$draw = intval($_POST['draw'] ?? 1);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 25);
$searchValue = $_POST['search']['value'] ?? '';
$orderColumn = intval($_POST['order'][0]['column'] ?? 6);
$orderDir = $_POST['order'][0]['dir'] ?? 'desc';

// Mapeo de columnas para ordenamiento
$columns = ['', 'image', 'title', 'categorias', 'author', 'status', 'created_at'];
$orderBy = $columns[$orderColumn] ?? 'created_at';

// Construcción de la consulta base
$whereConditions = ["p.deleted = 0"];
$params = [];

if (!$canViewOthers) {
    $whereConditions[] = "p.author_user = :user";
    $params[':user'] = $currentUser;
}

// Búsqueda global
if (!empty($searchValue)) {
    $whereConditions[] = "(p.title LIKE :search OR p.author LIKE :search OR c.name LIKE :search)";
    $params[':search'] = "%$searchValue%";
}

$whereSQL = implode(' AND ', $whereConditions);

// Consulta para contar registros totales (sin filtro)
$sqlTotal = "SELECT COUNT(DISTINCT p.id) as total FROM blog_posts p WHERE p.deleted = 0";
if (!$canViewOthers) {
    $sqlTotal .= " AND p.author_user = :user";
}
$stTotal = db()->prepare($sqlTotal);
if (!$canViewOthers) {
    $stTotal->execute([':user' => $currentUser]);
} else {
    $stTotal->execute();
}
$recordsTotal = $stTotal->fetch()['total'];

// Consulta para contar registros filtrados
$sqlFiltered = "
    SELECT COUNT(DISTINCT p.id) as total 
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id AND c.deleted = 0
    WHERE $whereSQL
";
$stFiltered = db()->prepare($sqlFiltered);
$stFiltered->execute($params);
$recordsFiltered = $stFiltered->fetch()['total'];

// Consulta principal con paginación
$sql = "
    SELECT 
        p.id,
        p.title,
        p.image,
        p.author,
        p.status,
        p.created_at,
        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categorias
    FROM blog_posts p
    LEFT JOIN blog_post_category pc ON pc.post_id = p.id
    LEFT JOIN blog_categories c ON c.id = pc.category_id AND c.deleted = 0
    WHERE $whereSQL
    GROUP BY p.id
    ORDER BY $orderBy $orderDir
    LIMIT :start, :length
";

$st = db()->prepare($sql);
$params[':start'] = $start;
$params[':length'] = $length;

// Bindear parámetros correctamente
foreach ($params as $key => $value) {
    if ($key === ':start' || $key === ':length') {
        $st->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $st->bindValue($key, $value);
    }
}

$st->execute();
$posts = $st->fetchAll();

// Formatear datos para DataTables
$data = [];
foreach ($posts as $p) {
    // Imagen
    $image = '';
    if ($p['image']) {
        $image = '<img src="' . htmlspecialchars($GLOBALS['url'] . '/' . $p['image']) . '" class="post-thumb">';
    } else {
        $image = '<span class="text-muted">Sin imagen</span>';
    }

    // Título truncado con tooltip
    $titleFull = htmlspecialchars($p['title']);
    $titleDisplay = mb_strlen($p['title']) > 60 
        ? htmlspecialchars(mb_substr($p['title'], 0, 60)) . '...' 
        : $titleFull;
    
    $titleHtml = '<div class="post-title" title="' . $titleFull . '">' 
                . '<strong>' . $titleDisplay . '</strong>'
                . '</div>';

    // Badge de estado
    $badge = $p['status'] === 'published' 
        ? '<span class="badge bg-success">Publicado</span>'
        : '<span class="badge bg-secondary">Borrador</span>';

    // Acciones
    $actions = '
        <a class="btn btn-sm btn-outline-primary" href="' . $GLOBALS['url'] . '/admin/blog/edit.php?id=' . (int)$p['id'] . '" title="Editar">
            <i class="fa fa-pencil"></i>
        </a>
        <button class="btn-trash btn-delete" data-id="' . (int)$p['id'] . '" data-name="' . htmlspecialchars($p['title']) . '" title="Eliminar">
            <i class="fa fa-trash"></i>
        </button>
    ';

    $data[] = [
        '<input type="checkbox" class="chkPost form-check-input" value="' . (int)$p['id'] . '">',
        $image,
        $titleHtml,
        htmlspecialchars($p['categorias'] ?: '—'),
        htmlspecialchars($p['author']),
        $badge,
        $p['created_at'],
        $actions
    ];
}

// Respuesta JSON
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);