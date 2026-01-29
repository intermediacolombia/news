<?php
// Activar reporte de errores para debugging (comentar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);

// Capturar errores para devolverlos en JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit;
});

try {
    require_once __DIR__ . '/../../inc/config.php';
    require_once __DIR__ . '/../login/session.php';
    
    header('Content-Type: application/json');
    
    // Verificar que la tabla existe
    $checkTable = db()->query("SHOW TABLES LIKE 'institutional_pages'");
    if($checkTable->rowCount() == 0) {
        throw new Exception('La tabla institutional_pages no existe. Ejecute database.sql primero.');
    }
} catch(Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
    exit;
}

// Parámetros de DataTables
$draw = (int)($_POST['draw'] ?? 1);
$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 10);
$searchValue = $_POST['search']['value'] ?? '';
$orderColumnIndex = (int)($_POST['order'][0]['column'] ?? 6);
$orderDir = $_POST['order'][0]['dir'] ?? 'desc';

try {
    // Mapeo de columnas
    $columns = ['', 'title', 'slug', 'page_type', 'status', 'author_name', 'updated_at', ''];
    $orderColumn = $columns[$orderColumnIndex] ?? 'updated_at';
    
    // Validar columna de orden
    $validColumns = ['title', 'slug', 'page_type', 'status', 'author_name', 'updated_at'];
    if(!in_array($orderColumn, $validColumns)) {
        $orderColumn = 'updated_at';
    }
    
    // Validar dirección de orden
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
    
    // Construir query base
    $where = "1=1";
    $params = [];
    
    if(!empty($searchValue)) {
        $where .= " AND (ip.title LIKE ? OR ip.slug LIKE ? OR ip.page_type LIKE ?)";
        $searchParam = "%{$searchValue}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Total de registros sin filtro
    $totalQuery = "SELECT COUNT(*) as total FROM institutional_pages";
    $totalResult = db()->query($totalQuery);
    $totalRecords = $totalResult->fetchColumn();
    
    // Total de registros filtrados
    $filteredQuery = "SELECT COUNT(*) as total FROM institutional_pages ip WHERE {$where}";
    if(!empty($params)) {
        $stmt = db()->prepare($filteredQuery);
        $stmt->execute($params);
        $filteredRecords = $stmt->fetchColumn();
    } else {
        $filteredRecords = $totalRecords;
    }
    
    // Query principal con paginación
    $sql = "SELECT ip.*, u.nombre as author_name 
            FROM institutional_pages ip
            LEFT JOIN usuarios u ON ip.created_by = u.id
            WHERE {$where}
            ORDER BY ip.{$orderColumn} {$orderDir}
            LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    
    // Bind de parámetros de búsqueda (si existen)
    $paramIndex = 1;
    if(!empty($searchValue)) {
        $searchParam = "%{$searchValue}%";
        $stmt->bindValue($paramIndex++, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue($paramIndex++, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue($paramIndex++, $searchParam, PDO::PARAM_STR);
    }
    
    // Bind de LIMIT y OFFSET como integers
    $stmt->bindValue(':limit', $length, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $start, PDO::PARAM_INT);
    
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    $data = [];
    foreach($results as $row) {
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
    
} catch(PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
} catch(Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}