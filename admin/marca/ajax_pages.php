<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../login/session.php';

header('Content-Type: application/json');

// Parámetros de DataTables
$draw = (int)($_POST['draw'] ?? 1);
$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 10);
$searchValue = $_POST['search']['value'] ?? '';
$orderColumnIndex = (int)($_POST['order'][0]['column'] ?? 6);
$orderDir = $_POST['order'][0]['dir'] ?? 'desc';

// Mapeo de columnas
$columns = ['', 'title', 'slug', 'page_type', 'status', 'author_name', 'updated_at', ''];
$orderColumn = $columns[$orderColumnIndex] ?? 'updated_at';

// Construir query base
$where = "1=1";
$params = [];
$types = '';

if(!empty($searchValue)) {
    $where .= " AND (ip.title LIKE ? OR ip.slug LIKE ? OR ip.page_type LIKE ?)";
    $searchParam = "%{$searchValue}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

// Total de registros sin filtro
$totalQuery = "SELECT COUNT(*) as total FROM institutional_pages";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];

// Total de registros filtrados
$filteredQuery = "SELECT COUNT(*) as total FROM institutional_pages ip WHERE {$where}";
if(!empty($params)) {
    $stmt = $conn->prepare($filteredQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $filteredResult = $stmt->get_result();
    $filteredRecords = $filteredResult->fetch_assoc()['total'];
} else {
    $filteredRecords = $totalRecords;
}

// Query principal con paginación
$sql = "SELECT ip.*, u.nombre as author_name 
        FROM institutional_pages ip
        LEFT JOIN usuarios u ON ip.created_by = u.id
        WHERE {$where}
        ORDER BY {$orderColumn} {$orderDir}
        LIMIT ? OFFSET ?";

$types .= 'ii';
$params[] = $length;
$params[] = $start;

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()) {
    // Traducir tipos de página
    $pageTypes = [
        'general' => 'General',
        'about' => 'Quiénes Somos',
        'mission' => 'Misión y Visión',
        'history' => 'Historia',
        'organization' => 'Organigrama',
        'board' => 'Junta Directiva',
        'team' => 'Equipo',
        'values' => 'Valores',
        'policies' => 'Políticas'
    ];
    
    $pageType = $pageTypes[$row['page_type']] ?? 'General';
    
    // Estado
    $statusBadge = $row['status'] === 'published' 
        ? '<span class="badge bg-success">Publicado</span>'
        : '<span class="badge bg-secondary">Borrador</span>';
    
    // Fecha formateada
    $date = date('d/m/Y H:i', strtotime($row['updated_at']));
    
    // Acciones
    $actions = '
        <a href="edit.php?id=' . (int)$row['id'] . '" class="btn btn-sm btn-primary" title="Editar">
            <i class="fa fa-edit"></i>
        </a>
        <button class="btn btn-sm btn-danger btn-delete" 
                data-id="' . (int)$row['id'] . '" 
                data-name="' . htmlspecialchars($row['title'], ENT_QUOTES) . '" 
                title="Eliminar">
            <i class="fa fa-trash"></i>
        </button>
    ';
    
    $data[] = [
        '<input type="checkbox" class="form-check-input chkPage" value="' . (int)$row['id'] . '">',
        '<span class="page-title" title="' . htmlspecialchars($row['title']) . '">' . 
            htmlspecialchars($row['title']) . '</span>',
        htmlspecialchars($row['slug']),
        $pageType,
        $statusBadge,
        htmlspecialchars($row['author_name'] ?? 'Sistema'),
        $date,
        $actions
    ];
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $data
]);