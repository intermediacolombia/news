<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/login/session.php';

$permisopage = 'Editar Configuraciones';
require_once __DIR__ . '/login/restriction.php';

function repair_database() {
    $results = [
        'permissions' => [],
        'tables' => [],
        'errors' => []
    ];
    
    try {
        // 1. Agregar permisos faltantes
        $newPermissions = [
            [23, 'Ver Logs', 'Sistema'],
        ];
        
        foreach ($newPermissions as $perm) {
            list($id, $name, $category) = $perm;
            $stmt = db()->prepare("SELECT id FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                $insert = db()->prepare("INSERT INTO permissions (id, name, category, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $insert->execute([$id, $name, $category]);
                $results['permissions'][] = "Agregado: $name";
            } else {
                $results['permissions'][] = "Ya existe: $name";
            }
        }
        
        // 2. Crear tablas faltantes
        $tables = [
            'system_logs' => "
                CREATE TABLE IF NOT EXISTS `system_logs` (
                  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                  `user_id` int DEFAULT NULL,
                  `username` varchar(50) DEFAULT NULL,
                  `action` varchar(100) NOT NULL,
                  `description` text,
                  `entity_type` varchar(50) DEFAULT NULL,
                  `entity_id` bigint DEFAULT NULL,
                  `ip_address` varchar(45) DEFAULT NULL,
                  `user_agent` text,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_user_id` (`user_id`),
                  KEY `idx_action` (`action`),
                  KEY `idx_entity` (`entity_type`, `entity_id`),
                  KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];
        
        foreach ($tables as $tableName => $createSQL) {
            $stmt = db()->query("SHOW TABLES LIKE '$tableName'");
            if (!$stmt->fetch()) {
                db()->exec($createSQL);
                $results['tables'][] = "Creada tabla: $tableName";
            } else {
                $results['tables'][] = "Ya existe tabla: $tableName";
            }
        }
        
        // 3. Agregar columnas faltantes a tablas existentes (opcional)
        // Para futuras migraciones
        
    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }
    
    return $results;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repair') {
    header('Content-Type: application/json');
    $results = repair_database();
    echo json_encode($results);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reparar Base de Datos - <?= NOMBRE_SITIO ?></title>
    <?php require_once __DIR__ . '/inc/header.php'; ?>
    <style>
        .repair-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .repair-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            display: none;
        }
        .repair-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .repair-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .result-item {
            padding: 5px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .result-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/inc/menu.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="repair-card">
            <h3><i class="fa fa-database"></i> Reparar Base de Datos</h3>
            <p class="text-muted">
                Esta herramienta agregará automáticamente permisos y tablas faltantes 
                sin afectar los datos existentes.
            </p>
            
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <strong>Se repairará:</strong>
                <ul class="mb-0 mt-2">
                    <li>Permisos nuevos del sistema</li>
                    <li>Tablas nuevas necesarias</li>
                </ul>
            </div>
            
            <button type="button" class="btn btn-primary btn-lg w-100" onclick="runRepair()">
                <i class="fa fa-wrench"></i> Ejecutar Reparación
            </button>
            
            <div id="repairResult" class="repair-result"></div>
            
            <div class="mt-3 text-center">
                <a href="<?= URLBASE ?>/admin/config/" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Volver a Configuraciones
                </a>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/inc/menu-footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function runRepair() {
        Swal.fire({
            title: '¿Ejecutar reparación?',
            text: 'Se agregarán los elementos faltantes a la base de datos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, ejecutar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.querySelector('button.btn-primary');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Reparando...';
                
                fetch('repair_db.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=repair'
                })
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    const resultDiv = document.getElementById('repairResult');
                    
                    if (data.permissions && data.permissions.length > 0) {
                        html += '<h5>Permisos:</h5>';
                        data.permissions.forEach(p => {
                            html += `<div class="result-item">✓ ${p}</div>`;
                        });
                    }
                    
                    if (data.tables && data.tables.length > 0) {
                        html += '<h5 class="mt-3">Tablas:</h5>';
                        data.tables.forEach(t => {
                            html += `<div class="result-item">✓ ${t}</div>`;
                        });
                    }
                    
                    if (data.errors && data.errors.length > 0) {
                        html += '<h5 class="mt-3 text-danger">Errores:</h5>';
                        data.errors.forEach(e => {
                            html += `<div class="result-item text-danger">✗ ${e}</div>`;
                        });
                        resultDiv.className = 'repair-result repair-error';
                    } else {
                        resultDiv.className = 'repair-result repair-success';
                    }
                    
                    resultDiv.innerHTML = html;
                    resultDiv.style.display = 'block';
                    
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-wrench"></i> Ejecutar Reparación';
                    
                    Swal.fire('¡Completado!', 'La base de datos ha sido reparada.', 'success');
                })
                .catch(err => {
                    Swal.fire('Error', 'No se pudo completar la reparación.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-wrench"></i> Ejecutar Reparación';
                });
            }
        });
    }
    </script>
</body>
</html>
