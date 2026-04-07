<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

$permisopage = 'Ver Logs';
require_once __DIR__ . '/../login/restriction.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$filters = [];
$params = [];

if (!empty($_GET['action'])) {
    $filters[] = "action LIKE :action";
    $params[':action'] = '%' . $_GET['action'] . '%';
}

if (!empty($_GET['username'])) {
    $filters[] = "username LIKE :username";
    $params[':username'] = '%' . $_GET['username'] . '%';
}

if (!empty($_GET['entity_type'])) {
    $filters[] = "entity_type = :entity_type";
    $params[':entity_type'] = $_GET['entity_type'];
}

if (!empty($_GET['date_from'])) {
    $filters[] = "created_at >= :date_from";
    $params[':date_from'] = $_GET['date_from'] . ' 00:00:00';
}

if (!empty($_GET['date_to'])) {
    $filters[] = "created_at <= :date_to";
    $params[':date_to'] = $_GET['date_to'] . ' 23:59:59';
}

$whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';

$countSql = "SELECT COUNT(*) FROM system_logs {$whereClause}";
$stmtCount = db()->prepare($countSql);
$stmtCount->execute($params);
$totalLogs = $stmtCount->fetchColumn();

$totalPages = ceil($totalLogs / $limit);

$sql = "SELECT * FROM system_logs {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$actionsSql = "SELECT DISTINCT action FROM system_logs ORDER BY action";
$actions = db()->query($actionsSql)->fetchAll(PDO::FETCH_COLUMN);

$entityTypesSql = "SELECT DISTINCT entity_type FROM system_logs WHERE entity_type IS NOT NULL ORDER BY entity_type";
$entityTypes = db()->query($entityTypesSql)->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logs del Sistema - <?= NOMBRE_SITIO ?></title>
    <?php require_once __DIR__ . '/../inc/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        .filter-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .log-table { font-size: 0.9rem; }
        .log-table th { background: #f8f9fa; font-weight: 600; }
        .log-table td { vertical-align: middle; }
        .log-action {
            font-weight: 600;
            color: var(--primary-color, #E21F0C);
        }
        .log-description {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .log-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .badge-action {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../inc/menu.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa fa-history"></i> Logs del Sistema</h2>
            <div>
                <button class="btn btn-outline-secondary btn-sm" onclick="exportLogs()">
                    <i class="fa fa-download"></i> Exportar CSV
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="clearOldLogs()">
                    <i class="fa fa-trash"></i> Limpiar Logs Antiguos
                </button>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Acción</label>
                    <input type="text" name="action" class="form-control" placeholder="Buscar acción..." value="<?= htmlspecialchars($_GET['action'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control" placeholder="Buscar usuario..." value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo Entidad</label>
                    <select name="entity_type" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($entityTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= ($_GET['entity_type'] ?? '') === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover log-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>Entidad</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No se encontraron logs</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td>
                                    <div><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                    <div class="log-meta"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td>
                                    <?php if ($log['username']): ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars($log['username']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">Sistema</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge-action"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td>
                                    <span class="log-description" title="<?= htmlspecialchars($log['description'] ?? '') ?>">
                                        <?= htmlspecialchars($log['description'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['entity_type']): ?>
                                    <small>
                                        <strong><?= htmlspecialchars($log['entity_type']) ?></strong>
                                        <?php if ($log['entity_id']): ?>
                                        #<?= $log['entity_id'] ?>
                                        <?php endif; ?>
                                    </small>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">Primera</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Siguiente</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">Última</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="text-center text-muted">
                    Mostrando <?= ($offset + 1) ?> - <?= min($offset + $limit, $totalLogs) ?> de <?= $totalLogs ?> registros
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../inc/menu-footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function exportLogs() {
        window.location.href = 'export_logs.php?' + window.location.search.substring(1);
    }

    function clearOldLogs() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se eliminarán los logs anteriores a 30 días',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('clear_logs.php', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    Swal.fire('Éxito', data.message, 'success').then(() => location.reload());
                })
                .catch(err => Swal.fire('Error', 'No se pudo completar la acción', 'error'));
            }
        });
    }
    </script>
</body>
</html>
